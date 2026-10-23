<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A purchase from a supplier. It starts as a pending order (no stock, not
 * counted in credit, reports or stock value) and becomes a purchase once
 * it is received — see App\Actions\Purchases\ReceivePurchaseOrder.
 *
 * Queries only see received purchases by default; use withOrders() to
 * include pending and cancelled orders.
 */
class PurchaseInvoice extends Model
{
    public const PENDING = 'pending';
    public const RECEIVED = 'received';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'shop_id',
        'supplier_id',
        'invoice_number',
        'total_amount',
        'amount_paid',
        'remaining_amount',
        'payment_type',
        'status',
        'ordered_at',
        'received_at',
        'received_by',
        'purchased_at',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('received', fn (Builder $q) => $q->where($q->qualifyColumn('status'), self::RECEIVED));
    }

    /** Every purchase, including pending and cancelled orders. */
    public static function withOrders(): Builder
    {
        return static::withoutGlobalScope('received');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->withoutGlobalScope('received')->where($query->qualifyColumn('status'), self::PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    /** Invoice links (view, print, PDF, email) also work for orders. */
    public function resolveRouteBinding($value, $field = null)
    {
        return static::withOrders()->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    // روابط (relationships)

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_invoice_id');
    }

    public function payments()
    {

         return $this->hasMany(CreditPayment::class, 'purchase_invoice_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(Users::class, 'received_by');
    }
}
