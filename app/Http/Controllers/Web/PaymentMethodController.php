<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Settings › Payment Methods: the ways customers can pay, offered on the POS
 * and the mobile app.
 */
class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::withCount('sales')->ordered()->get();

        return view('pages.settings.payment_methods', [
            'methods' => $methods,
            'categories' => PaymentMethod::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] ??= (int) PaymentMethod::max('sort_order') + 10;

        PaymentMethod::create($data);

        return redirect()->route('settings.payment-methods.index')->with('success', "Payment method \"{$data['name']}\" added.");
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $this->validated($request, $paymentMethod);

        if ($this->isLastActiveCash($paymentMethod, $data)) {
            return back()->withInput()->with('error', 'Keep at least one active Cash method — the POS needs it.');
        }

        $paymentMethod->update($data);

        return redirect()->route('settings.payment-methods.index')->with('success', "Payment method \"{$paymentMethod->name}\" saved.");
    }

    /** Turn a method on/off from the list. */
    public function toggle(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->is_active && $this->isLastActiveCash($paymentMethod)) {
            return back()->with('error', 'Keep at least one active Cash method — the POS needs it.');
        }

        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        return back()->with('success', "\"{$paymentMethod->name}\" is now " . ($paymentMethod->is_active ? 'active' : 'turned off') . '.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $used = $paymentMethod->sales()->count();
        if ($used) {
            return back()->with('error', "\"{$paymentMethod->name}\" is used on {$used} sale(s), so it can't be deleted. Turn it off instead.");
        }
        if ($this->isLastActiveCash($paymentMethod)) {
            return back()->with('error', 'Keep at least one active Cash method — the POS needs it.');
        }

        $paymentMethod->delete();

        return back()->with('success', "Payment method \"{$paymentMethod->name}\" deleted.");
    }

    private function validated(Request $request, ?PaymentMethod $method = null): array
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100', Rule::unique('payment_methods', 'name')->ignore($method?->id)],
            'category'       => ['required', Rule::in(array_keys(PaymentMethod::CATEGORIES))],
            'account_name'   => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:255',
            'sort_order'     => 'nullable|integer|min:0|max:100000',
            'is_active'      => 'nullable|boolean',
        ], [
            'name.unique' => 'A payment method with this name already exists.',
        ]);

        $data['name'] = trim($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** Would this change leave the POS with no active cash method? */
    private function isLastActiveCash(PaymentMethod $method, array $newData = []): bool
    {
        if ($method->category !== 'cash' || !$method->is_active) {
            return false;
        }
        // Still an active cash method after the change → fine.
        if (($newData['category'] ?? 'cash') === 'cash' && ($newData['is_active'] ?? false)) {
            return false;
        }

        return !PaymentMethod::active()->where('category', 'cash')->whereKeyNot($method->id)->exists();
    }
}
