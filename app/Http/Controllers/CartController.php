<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;

class CartController extends Controller
{
    // Add or increment item in cart
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'discount'   => 'required|numeric|min:0',
        ]);

        $product = Products::findOrFail($request->product_id);

        $cart = session()->get('cart', []);
        $currentQtyInCart = $cart[$product->id]['qty'] ?? 0;
        $availableStock = $product->quantity - $currentQtyInCart;

        if ($request->quantity > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Not enough stock. Remaining: {$availableStock}"
            ]);
        }

        $totalDiscount = ($cart[$product->id]['discount'] ?? 0) + $request->discount;
        $totalPrice = ($currentQtyInCart + $request->quantity) * $product->selling_price;

        if ($totalDiscount > $totalPrice) {
            return response()->json([
                'success' => false,
                'message' => "Discount exceeds total price"
            ]);
        }

        $cart[$product->id] = [
            'name' => $product->name,
            'price' => $product->selling_price,
            'qty' => $currentQtyInCart + $request->quantity,
            'discount' => $totalDiscount,
        ];

        session()->put('cart', $cart);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        unset($cart[$request->product_id]);
        session()->put('cart', $cart);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    public function clear()
    {
        session()->forget('cart');
        return response()->json(['success' => true, 'cart' => []]);
    }

    public function getCart()
    {
        return response()->json(session()->get('cart', []));
    }
}
