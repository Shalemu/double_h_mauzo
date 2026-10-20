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
        return $this->belongsTo(Staff::class, 'staff_id');
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
}
