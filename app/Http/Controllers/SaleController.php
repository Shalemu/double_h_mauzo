<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\Sales\CheckoutSale;
use App\DTO\Sales\SaleData;
use App\Models\Sale;
use App\Models\Shops;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailySalesExport;
use App\Exports\DailySalesPdfExport;

class SaleController extends Controller
{
    protected $staff;

    public function __construct()
    {
        // Middleware to get the currently logged-in staff user
        $this->middleware(function ($request, $next) {
            $this->staff = auth()->guard('staff')->user();
            return $next($request);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF SALES LIST
    |--------------------------------------------------------------------------
    */
   public function index($shopId)
{
    $shop = Shops::findOrFail($shopId);

    // Staff restriction
    if (auth()->guard('staff')->check()) {
        $staff = auth()->guard('staff')->user();

        if ($staff->shop_id != $shop->id) {
            abort(403, 'Unauthorized');
        }

        $salesQuery = Sale::where('shop_id', $shop->id)
            ->where('staff_id', $staff->id); 
    } else {
        // Admin sees all
        $salesQuery = Sale::where('shop_id', $shop->id);
    }

    $sales = $salesQuery->orderBy('created_at', 'desc')->get();

    $salesByDate = $sales->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'))
        ->map(fn($group, $date) => [
            'date' => $date,
            'total' => $group->sum('total')
        ])
        ->values();

    return view('pages.staff.sales.index', compact('shop', 'salesByDate'));
}

    /*
    |--------------------------------------------------------------------------
    | STAFF DAILY SALES DETAIL
    |--------------------------------------------------------------------------
    */
public function detail($shopId, $date)
{
    $shop = Shops::findOrFail($shopId);

    // STAFF VIEW
    if (auth()->guard('staff')->check()) {
        $staff = auth()->guard('staff')->user();

        if ($staff->shop_id != $shop->id) {
            abort(403, 'Unauthorized');
        }

        $sales = Sale::with(['items.product', 'staff'])
            ->where('shop_id', $shop->id)
            ->where('staff_id', $staff->id) 
            ->whereDate('created_at', $date)
            ->get();

        $itemRows = $this->aggregateItems($sales);

        return view('pages.staff.sales.detail', compact('shop', 'date', 'itemRows'));
    }

    // ADMIN VIEW
    if (auth()->guard('web')->check()) {
        $sales = Sale::with(['items.product','staff'])
            ->where('shop_id', $shop->id)
            ->whereDate('created_at', $date)
            ->get();

        $itemRows = $this->aggregateItems($sales);

        // Loaded into the shop page by AJAX = bare fragment; opened directly = full page.
        return view(request()->ajax() ? 'pages.sales.detail' : 'pages.sales.detail_page', compact('shop', 'date', 'itemRows'));
    }

    abort(403, 'Unauthorized access.');
}



    /*
    |--------------------------------------------------------------------------
    | EXPORT DAILY SALES TO EXCEL
    |--------------------------------------------------------------------------
    */
    public function exportExcel($shopId, $date)
    {
        [$shop, $itemRows] = $this->exportRows($shopId, $date);

        return Excel::download(new DailySalesExport($itemRows), "daily_sales_{$date}.xlsx");
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT DAILY SALES TO PDF
    |--------------------------------------------------------------------------
    */
    public function exportPdf($shopId, $date)
    {
        [$shop, $itemRows] = $this->exportRows($shopId, $date);

        return (new DailySalesPdfExport($shop, $date, $itemRows))
            ->download("daily_sales_{$date}.pdf");
    }

    /*
    |--------------------------------------------------------------------------
    | CHECKOUT (CREATE SALE)
    |--------------------------------------------------------------------------
    */
    public function checkout(Request $request, $shopId, CheckoutSale $checkoutSale)
    {
        if (empty($request->input('cart', []))) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.']);
        }

        $staff = $this->staff;
        $admin = null;

        if (!$staff) {
            // No staff session — this must be an admin-tier account
            // selling directly from the admin POS.
            $admin = Auth::user();
            if (!$admin || !$admin->isAdminTier()) {
                abort(403, 'You are not authorized to record a sale.');
            }
            if (!Shops::whereKey($shopId)->exists()) {
                abort(404, 'Shop not found.');
            }
        }

        if (Shops::whereKey($shopId)->stores()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This is a store — it holds stock only and cannot sell. Transfer the stock to a shop first.',
            ], 422);
        }

        $validated = $request->validate([
            'cart' => 'required|array|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|string',
            'payment_type' => 'nullable|string',
            'received_amount' => 'nullable|numeric|min:0',
            'bill_discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
        ]);

        try {
            $sale = $checkoutSale->execute(SaleData::fromArray((int) $shopId, $validated, $staff?->id, $admin?->id));
        } catch (\Throwable $e) {
            // Rule problems (not enough cash/stock, no customer for credit…) come back
            // in the shape the POS already shows: "Checkout failed: …".
            if (!$e instanceof \DomainException) {
                report($e);
            }

            return response()->json(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'message' => 'Sale completed successfully!',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: Rows for the daily Excel/PDF exports
    |--------------------------------------------------------------------------
    | Staff may only export their own shop and their own sales (same as the
    | detail page they export from); ?type=retail|wholesale narrows the rows.
    */
    private function exportRows($shopId, $date): array
    {
        $shop = Shops::findOrFail($shopId);
        $staff = auth()->guard('staff')->user();

        if ($staff && $staff->shop_id != $shop->id) {
            abort(403, 'Unauthorized');
        }

        $sales = Sale::with(['items.product', 'staff'])
            ->where('shop_id', $shop->id)
            ->when($staff, fn ($q) => $q->where('staff_id', $staff->id))
            ->whereDate('created_at', $date)
            ->get();

        $rows = $this->aggregateItems($sales);

        $type = request()->query('type');
        if (in_array($type, ['retail', 'wholesale'], true)) {
            $rows = array_values(array_filter($rows, fn ($row) => ($row['sale_type'] ?? 'retail') === $type));
        }

        return [$shop, $rows];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: Aggregate Items
    |--------------------------------------------------------------------------
    */
    private function aggregateItems($sales)
    {
        $rows = [];

        foreach ($sales as $sale) {
            $staffName = $sale->staff->full_name ?? 'Unknown';
            foreach ($sale->items as $item) {
                $key = $item->product->id . '|' . $staffName;

                if (!isset($rows[$key])) {
                    $rows[$key] = [
                        'product' => $item->product->name,
                        'quantity' => 0,
                        'revenue' => 0,
                        'staff' => $staffName,
                        'sale_type' => $item->sale_type ?? 'retail',
                    ];
                }

                $rows[$key]['quantity'] += $item->quantity;
                $rows[$key]['revenue'] += $item->price * $item->quantity;
            }
        }

        return array_values($rows);
    }
}
