<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One distributor trip: stock taken out of a store, later settled with the
 * returned units going back into the store and the rest recorded as a sale.
 */
class Distribution extends Model
{
    protected $fillable = [
        'store_id', 'staff_id', 'issued_by', 'status', 'note',
        'settled_at', 'settled_by', 'sale_id', 'total_value', 'amount_received', 'balance',
    ];

    protected $casts = [
        'settled_at' => 'datetime',
    ];

    public function isOut(): bool
    {
        return $this->status === \App\Enums\DistributionStatus::Out->value;
    }

    public function store()
    {
        return $this->belongsTo(Shops::class, 'store_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id')->withTrashed(); // keep history of removed staff
    }

    public function issuedBy()
    {
        return $this->belongsTo(Users::class, 'issued_by');
    }

    public function settledBy()
    {
        return $this->belongsTo(Users::class, 'settled_by');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(DistributionItem::class);
    }

    public function payments()
    {
        return $this->hasMany(DistributionPayment::class);
    }

    /** Sales the distributor recorded on the POS during this trip. */
    public function posSales()
    {
        return $this->hasMany(Sale::class)->where(fn ($q) => $q->whereNull('payment_method')->orWhere('payment_method', '!=', \App\Enums\PaymentMethod::Distribution->value));
    }

    /** The open trip a distributor is selling from, if any (one at a time). */
    public static function openFor(?int $staffId): ?self
    {
        return $staffId
            ? static::where('staff_id', $staffId)->where('status', \App\Enums\DistributionStatus::Out->value)->latest('id')->first()
            : null;
    }

    /** Cash the distributor collected on POS sales (received minus change given back). */
    public function posCashCollected(): float
    {
        return (float) $this->posSales()->sum(\Illuminate\Support\Facades\DB::raw('received_amount - change_amount'));
    }
}
