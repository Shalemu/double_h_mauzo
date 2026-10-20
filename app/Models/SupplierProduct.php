<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A product assigned to a supplier, with the price that supplier charges.
 */
class SupplierProduct extends Model
{
    protected $fillable = ['supplier_id', 'product_id', 'supplier_price', 'assigned_by'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(Users::class, 'assigned_by');
    }
}
