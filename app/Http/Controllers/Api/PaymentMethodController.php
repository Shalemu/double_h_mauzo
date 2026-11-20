<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    /**
     * GET /api/payment-methods — the active payment methods, in the order set
     * in Settings › Payment Methods. Send the chosen one's id as
     * payment_method_id on POST /api/sales.
     *
     * category: cash (must cover the total) | mobile | bank | credit (needs a customer)
     */
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::active()->ordered()->get()->map(fn (PaymentMethod $m) => [
            'id'             => $m->id,
            'name'           => $m->name,
            'category'       => $m->category,
            'account_name'   => $m->account_name,
            'account_number' => $m->account_number,
        ]);

        return response()->json(['data' => $methods]);
    }
}
