<?php

namespace App\Models;

use App\Enums\PaymentMethod as PaymentCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * A way to pay, managed in Settings › Payment Methods (e.g. "CRDB – 0150…").
 *
 * `category` is one of cash / mobile / bank / credit (App\Enums\PaymentMethod)
 * and decides how a sale behaves: cash must cover the total, credit needs a
 * customer and records a debt, mobile/bank are paid in full.
 */
class PaymentMethod extends Model
{
    protected $fillable = ['name', 'category', 'account_name', 'account_number', 'notes', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Categories a method can be given (distribution is internal only). */
    public const CATEGORIES = [
        'cash' => 'Cash',
        'mobile' => 'Mobile money',
        'bank' => 'Bank',
        'credit' => 'Credit (customer pays later)',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    /** "CRDB · 0150123456" — what the cashier sees under the choice. */
    public function accountLine(): ?string
    {
        $parts = array_filter([$this->account_name, $this->account_number]);

        return $parts ? implode(' · ', $parts) : null;
    }

    /**
     * Work out a sale's payment from the checkout input.
     *
     * With payment_method_id (web POS, new app versions) the method decides
     * the category and the label. Without it (older app versions) the old
     * payment_method + payment_type fields are used as sent.
     *
     * @return array{0: PaymentCategory, 1: ?string, 2: ?int}  category, label, method id
     */
    public static function resolveForSale(array $data): array
    {
        if (!empty($data['payment_method_id'])) {
            $method = static::active()->find($data['payment_method_id']);
            if (!$method) {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'This payment method does not exist or has been turned off.',
                ]);
            }

            return [PaymentCategory::from($method->category), $method->name, $method->id];
        }

        $category = PaymentCategory::tryFrom((string) ($data['payment_method'] ?? '')) ?? PaymentCategory::Cash;

        return [$category, ($data['payment_type'] ?? null) ?: null, null];
    }
}
