<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Products;
use Carbon\Carbon;
use App\Models\Shops;
use App\Exports\DailySalesExport;
use App\Exports\DailySalesPdfExport;
use Maatwebsite\Excel\Facades\Excel;


class SaleController extends Controller
{
    protected $staff;

public function __construct()
{
    $this->middleware(function ($request, $next) {
        if (!auth()->check() && !auth()->guard('staff')->check()) {
            return redirect()->route('login');
        }

        // Set staff if logged in as staff
        $this->staff = auth()->guard('staff')->user();

        return $next($request);
    });
}


    /**
     * Display sales, optionally filtered by date
     */
public function detail($shopId, $date)
{
    $shop = Shops::findOrFail($shopId);

    if ($this->staff && $this->staff->shop_id != $shop->id) {
        abort(403, 'Unauthorized access to this shop.');
    }

    $sales = $shop->sales()
        ->whereDate('sales.created_at', $date)
        ->with('items.product', 'staff') // staff = aliyeuza
        ->get();

    $itemRows = [];

    // Aggregate by product + staff
    foreach ($sales as $sale) {
        $staffName = $sale->staff->full_name ?? 'Unknown';

        foreach ($sale->items as $item) {
            $key = $item->product->id . '|' . $staffName;

            if (!isset($itemRows[$key])) {
                $itemRows[$key] = [
                    'product' => $item->product->name,
                    'quantity' => 0,
                    'revenue' => 0,
                    'staff' => $staffName,
                ];
            }

            $itemRows[$key]['quantity'] += $item->quantity;
            $itemRows[$key]['revenue'] += $item->price * $item->quantity;
        }
    }

    // Re-index array for Blade
    $itemRows = array_values($itemRows);

    return view('dashboard.sales.detail', compact('shop', 'date', 'itemRows'));
}


public function detailItems($shopId, $date, Request $request)
{
    $shop = Shops::findOrFail($shopId);

    $productName = $request->query('product');
    $staffName = $request->query('staff');

    $sales = $shop->sales()
        ->whereDate('sales.created_at', $date)
        ->whereHas('staff', function($q) use ($staffName) {
            $q->whereRaw("CONCAT(first_name,' ',last_name) = ?", [$staffName]);
        })
        ->with(['items.product'])
        ->get();

    // Filter only items matching the product
    $filteredItems = [];
    foreach ($sales as $sale) {
        foreach ($sale->items as $item) {
            if ($item->product->name === $productName) {
                $filteredItems[] = [
                    'time' => $sale->created_at->format('H:i'),
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                ];
            }
        }
    }

    return view('dashboard.sales.partials.detail-items', compact('filteredItems', 'productName', 'staffName'));
}


public function exportExcel($shopId, $date)
{
    $shop = Shops::findOrFail($shopId);

    $sales = $shop->sales()
        ->whereDate('sales.created_at', $date)
        ->with('items.product', 'staff')
        ->get();

    $itemRows = [];

    // Aggregate by product + staff
    foreach ($sales as $sale) {
        $staffName = $sale->staff->full_name ?? 'Unknown';

        foreach ($sale->items as $item) {
            $key = $item->product->id . '|' . $staffName;

            if (!isset($itemRows[$key])) {
                $itemRows[$key] = [
                    'product' => $item->product->name,
                    'quantity' => 0,
                    'revenue' => 0,
                    'staff' => $staffName,
                ];
            }

            $itemRows[$key]['quantity'] += $item->quantity;
            $itemRows[$key]['revenue'] += $item->price * $item->quantity;
        }
    }

    $itemRows = array_values($itemRows);

    return Excel::download(new DailySalesExport($itemRows), 'daily_sales.xlsx');
}

public function exportPdf($shopId, $date)
{
    $shop = Shops::findOrFail($shopId);

    $sales = $shop->sales()
        ->whereDate('sales.created_at', $date)
        ->with('items.product', 'staff')
        ->get();

    $itemRows = [];

    // Aggregate by product + staff
    foreach ($sales as $sale) {
        $staffName = $sale->staff->full_name ?? 'Unknown';

        foreach ($sale->items as $item) {
            $key = $item->product->id . '|' . $staffName;

            if (!isset($itemRows[$key])) {
                $itemRows[$key] = [
                    'product' => $item->product->name,
                    'quantity' => 0,
                    'revenue' => 0,
                    'staff' => $staffName,
                ];
            }

            $itemRows[$key]['quantity'] += $item->quantity;
            $itemRows[$key]['revenue'] += $item->price * $item->quantity;
        }
    }

    $itemRows = array_values($itemRows);

    return (new DailySalesPdfExport($shop, $date, $itemRows))->download();
}

    /**
     * Complete Sale / Checkout
     */
    public function checkout(Request $request)
    {
        $cart = $request->input('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ]);
        }

        // Validate input
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|string',
            'payment_type' => 'nullable|string',
            'received_amount' => 'nullable|numeric|min:0',
            'bill_discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'receipt' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $staff = auth()->guard('staff')->user();
            $billDiscount = $request->bill_discount ?? 0;
            $shipping = $request->shipping ?? 0;

            // Calculate subtotal
            $subTotal = collect($cart)->sum(fn($item) => ($item['qty'] * $item['price']) - ($item['discount'] ?? 0));

            $grandTotal = $subTotal - $billDiscount + $shipping;
            $paymentMethod = $request->payment_method;
            $paymentType = $request->payment_type;
            $received = $request->received_amount ?? 0;

            $remaining = 0;
            $change = 0;

            // Handle payment methods
            if ($paymentMethod === 'cash') {
                if ($received < $grandTotal) {
                    throw new \Exception("Received amount is less than total.");
                }
                $change = $received - $grandTotal;
            }

            if ($paymentMethod === 'credit') {
                if (!$request->customer_id) {
                    throw new \Exception("Customer required for credit sale.");
                }
                $remaining = max(0, $grandTotal - $received);
            }

            if (in_array($paymentMethod, ['bank', 'mobile']) && !$paymentType) {
                throw new \Exception("Please select payment type.");
            }

            // Create the Sale
            $sale = Sale::create([
                'staff_id' => $staff->id,
                'customer_id' => $request->customer_id,
                'bill_discount' => $billDiscount,
                'shipping' => $shipping,
                'payment_method' => $paymentMethod,
                'payment_type' => $paymentType,
                'received_amount' => $received,
                'remaining_amount' => $remaining,
                'change_amount' => $change,
                'total' => $grandTotal,
            ]);

            // Process each cart item
            foreach ($cart as $item) {
                $product = Products::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product ID {$item['product_id']} not found.");
                }

                if ($item['qty'] > $product->quantity) {
                    throw new \Exception("Not enough stock for {$product->name}. Remaining: {$product->quantity}");
                }

                // Decrement stock
                $product->decrement('quantity', $item['qty']);

                // Create SaleItem record
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
}
