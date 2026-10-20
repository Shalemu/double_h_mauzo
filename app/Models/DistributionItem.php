<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionItem extends Model
{
    protected $fillable = ['distribution_id', 'product_id', 'product_name', 'quantity_taken', 'quantity_returned', 'price'];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    // Units the distributor gave out (not brought back).
    public function getQuantitySoldAttribute(): int
    {
        return $this->quantity_taken - $this->quantity_returned;
    }
}
