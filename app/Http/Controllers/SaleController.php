<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Products;
use App\Models\Shops;
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

    return view('dashboard.staff.sales.index', compact('shop', 'salesByDate'));
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

        return view('dashboard.staff.sales.detail', compact('shop', 'date', 'itemRows'));
    }

    // ADMIN VIEW
    if (auth()->guard('web')->check()) {
        $sales = Sale::with(['items.product','staff'])
            ->where('shop_id', $shop->id)
            ->whereDate('created_at', $date)
            ->get();

        $itemRows = $this->aggregateItems($sales);

        return view('dashboard.sales.detail', compact('shop', 'date', 'itemRows'));
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
        $shop = Shops::findOrFail($shopId);

        $sales = Sale::with(['items.product', 'staff'])
            ->where('shop_id', $shop->id)
            ->whereDate('created_at', $date)
            ->get();

        $itemRows = $this->aggregateItems($sales);

        return Excel::download(new DailySalesExport($itemRows), "daily_sales_{$date}.xlsx");
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT DAILY SALES TO PDF
    |--------------------------------------------------------------------------
    */
    public function exportPdf($shopId, $date)
    {
        $shop = Shops::findOrFail($shopId);

        $sales = Sale::with(['items.product', 'staff'])
            ->where('shop_id', $shop->id)
            ->whereDate('created_at', $date)
            ->get();

        $itemRows = $this->aggregateItems($sales);

        return (new DailySalesPdfExport($shop, $date, $itemRows))
            ->download("daily_sales_{$date}.pdf");
    }

    /*
    |--------------------------------------------------------------------------
    | CHECKOUT (CREATE SALE)
    |--------------------------------------------------------------------------
    */
    public function checkout(Request $request, $shopId)
    {
        $cart = $request->input('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty.'
            ]);
        }

        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|string',
            'payment_type' => 'nullable|string',
            'received_amount' => 'nullable|numeric|min:0',
            'bill_discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $staff = $this->staff;

            $billDiscount = $request->bill_discount ?? 0;
            $shipping = $request->shipping ?? 0;

            $subTotal = collect($cart)->sum(fn($item) =>
                ($item['qty'] * $item['price']) - ($item['discount'] ?? 0)
            );

            $grandTotal = $subTotal - $billDiscount + $shipping;
            $received = $request->received_amount ?? 0;
            $remaining = 0;
            $change = 0;

            if ($request->payment_method === 'cash') {
                if ($received < $grandTotal) {
                    throw new \Exception("Received amount is less than total.");
                }
                $change = $received - $grandTotal;
            }

            if ($request->payment_method === 'credit') {
                if (!$request->customer_id) {
                    throw new \Exception("Customer required for credit sale.");
                }
                $remaining = max(0, $grandTotal - $received);
            }

            // Create sale
            $sale = Sale::create([
                'shop_id' => $shopId,
                'staff_id' => $staff->id,
                'customer_id' => $request->customer_id,
                'bill_discount' => $billDiscount,
                'shipping' => $shipping,
                'total' => $grandTotal,
                'payment_method' => $request->payment_method,
                'payment_type' => $request->payment_type,
                'received_amount' => $received,
                'remaining_amount' => $remaining,
                'change_amount' => $change,
            ]);

            // Save sale items
            foreach ($cart as $item) {
                $product = Products::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found.");
                }

                if ($item['qty'] > $product->quantity) {
                    throw new \Exception("Not enough stock for {$product->name}");
                }

                $product->decrement('quantity', $item['qty']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'message' => 'Sale completed successfully!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 500);
        }
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
                    ];
                }

                $rows[$key]['quantity'] += $item->quantity;
                $rows[$key]['revenue'] += $item->price * $item->quantity;
            }
        }

        return array_values($rows);
    }
}
