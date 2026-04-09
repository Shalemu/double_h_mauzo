<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\NewOrderNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\Users; // assuming admin users are in `users` table
class OrdersController extends Controller
{
    // Show staff order creation form
    public function create()
    {
        $products = Products::all(); // fetch products for staff to select
        return view('dashboard.staff.orders.create', compact('products'));
    }

    // Store order and items (selling price & sale type, no purchase price)
public function store(Request $request)
{
    $items = json_decode($request->items, true);
    if (!$items || !is_array($items)) {
        return back()->with('error', 'No items to submit.');
    }
    $request->merge(['items' => $items]);

    $request->validate([
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.sale_type' => 'required|string|in:retail,wholesale',
    ]);

    DB::transaction(function () use ($request) {
        $totalAmount = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['price']);

        $order = Order::create([
            'staff_id' => Auth::guard('staff')->user()->id,
            'shop_id'  => Auth::guard('staff')->user()->shop_id,
            'status'   => 'pending',
            'total_amount' => $totalAmount,
        ]);

        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'sale_type' => $item['sale_type'],
                'total' => $item['quantity'] * $item['price'],
            ]);
        }

        // Send email to all admin users
        $admins = \App\Models\Users::whereIn('role_id', [1, 2])->get(); 
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new NewOrderNotification($order));
        }
    });

    return back()->with('success', 'Order created successfully. Awaiting admin approval.');
}
    // Staff: list own orders
    public function myOrders()
    {
        $orders = Order::with('items.product')
            ->where('staff_id', Auth::guard('staff')->id())
            ->latest()
            ->get();

        return view('dashboard.staff.orders.my_orders', compact('orders'));
    }

    // Admin: list all orders
    public function index()
    {
        $orders = Order::with('items.product', 'staff')->latest()->get();
        return view('dashboard.staff.orders.index', compact('orders'));
    }

    // Admin: approve or reject order
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $order->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Order status updated.');
    }

    
    // Show single order details
    public function show(Order $order)
    {
        $order->load('items.product', 'staff');
        return view('dashboard.staff.orders.show', compact('order'));
    }
}