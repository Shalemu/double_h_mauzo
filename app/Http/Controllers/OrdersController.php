<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Products;
use App\Models\Shops;
use App\Models\Users;
use App\Mail\NewOrderNotification;
use App\Notifications\OrderRequested;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class OrdersController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Staff: request stock from admin
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('pages.staff.orders.create');
    }

    // Product lookup for the order form — shops can hold tens of thousands
    // of products, so the form searches instead of listing them all.
    public function searchProducts(Request $request)
    {
        $term = trim((string) $request->query('q'));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $products = Products::where('shop_id', Auth::guard('staff')->user()->shop_id)
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('item_code', 'like', "%{$term}%")
                ->orWhere('barcode', $term))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'quantity', 'selling_price', 'wholesale_price']);

        return response()->json($products);
    }

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
            'note' => 'nullable|string|max:1000',
        ]);

        $staff = Auth::guard('staff')->user();

        $order = DB::transaction(function () use ($request, $staff) {
            $order = Order::create([
                'staff_id' => $staff->id,
                'shop_id'  => $staff->shop_id,
                'status'   => 'pending',
                'note'     => $request->note,
                'total_amount' => collect($request->items)->sum(fn ($i) => $i['quantity'] * $i['price']),
            ]);

            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                ]);
            }

            return $order;
        });

        $this->notifyAdmins($order->load('staff', 'shop'));

        return redirect()->route('staff.orders.index')
            ->with('success', 'Order request sent. Admin has been notified for approval.');
    }

    // Staff: their own order requests
    public function index()
    {
        $orders = Order::with('items.product')
            ->where('staff_id', Auth::guard('staff')->user()->id)
            ->latest()
            ->get();

        return view('pages.staff.orders.index', compact('orders'));
    }

    public function myOrders()
    {
        return redirect()->route('staff.orders.index');
    }

    public function show(Order $order)
    {
        abort_unless((int) $order->staff_id === (int) Auth::guard('staff')->user()->id, 404);

        $order->load('items.product', 'staff', 'reviewer');
        return view('pages.staff.orders.show', compact('order'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin: review order requests
    |--------------------------------------------------------------------------
    */

    public function adminIndex(Request $request)
    {
        $status = $request->query('status', 'pending');
        $shopId = $request->query('shop', session('active_shop_id'));

        $query = Order::with('staff', 'shop', 'items')->latest();
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        $orders = $query->paginate(10)->withQueryString();
        $counts = Order::when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $shops = Shops::orderBy('name')->get(['id', 'name']);

        return view('pages.orders.admin_index', compact('orders', 'counts', 'shops', 'status', 'shopId'));
    }

    public function adminShow(Order $order)
    {
        $order->load('items.product', 'staff', 'shop', 'reviewer');
        return view('pages.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,purchased',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $needs = $request->status === 'purchased' ? 'order-requests.purchased' : 'order-requests.approve';
        abort_unless(Auth::user()->hasPermission($needs), 403, 'Your role does not have permission to do this.');

        $allowed = [
            'pending'  => ['approved', 'rejected'],
            'approved' => ['purchased', 'rejected'],
        ][$order->status] ?? [];

        if (!in_array($request->status, $allowed, true)) {
            return back()->with('error', "An order that is {$order->status} cannot be marked {$request->status}.");
        }

        $order->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note ?: $order->admin_note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($order->staff) {
            $order->staff->notify(new OrderStatusChanged($order));
        }

        return back()->with('success', "Order #{$order->id} marked {$order->status}.");
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications (shared by admin and staff)
    |--------------------------------------------------------------------------
    */

    public function openNotification(string $id)
    {
        $user = Auth::guard('staff')->user() ?? Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? url()->previous());
    }

    public function readAllNotifications()
    {
        $user = Auth::guard('staff')->user() ?? Auth::user();
        $user->unreadNotifications->markAsRead();

        return back();
    }

    private function notifyAdmins(Order $order): void
    {
        $admins = Users::whereIn('role_id', [1, 2])->orWhere('super_user', true)->get();

        Notification::send($admins, new OrderRequested($order));

        // Email is a bonus — a broken mail setup must not block the request.
        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new NewOrderNotification($order));
            } catch (\Throwable $e) {
                Log::warning('Order email failed: ' . $e->getMessage());
            }
        }
    }
}
