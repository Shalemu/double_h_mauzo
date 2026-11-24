<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One product on a distributor trip.
 *
 *   quantity_taken     left the store with the distributor
 *   quantity_recorded  sold through the POS during the trip (real sales)
 *   quantity_returned  brought back at settlement
 */
class DistributionItem extends Model
{
    protected $fillable = ['distribution_id', 'product_id', 'product_name', 'quantity_taken', 'quantity_recorded', 'quantity_returned', 'price'];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    // Units the distributor gave out (not brought back): POS sales + any not recorded.
    public function getQuantitySoldAttribute(): int
    {
        return $this->quantity_taken - $this->quantity_returned;
    }

    // Units the distributor still carries and can sell (while the trip is out).
    public function getQuantityLeftAttribute(): int
    {
        return max(0, $this->quantity_taken - $this->quantity_recorded - $this->quantity_returned);
    }

    // Gone but not recorded as a POS sale (settled as a sale at the end).
    public function getQuantityUnrecordedAttribute(): int
    {
        return max(0, $this->quantity_taken - $this->quantity_recorded - $this->quantity_returned);
    }
}
