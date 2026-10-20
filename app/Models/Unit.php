<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'allow_decimal',
        'base_unit_id',
        'multiplier',
        'shop_id',
        'admin_id',
    ];

    protected $casts = [
        'allow_decimal' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Products::class, 'unit_id');
    }

    // The unit this one is a multiple of (e.g. Carton → Piece).
    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    // Units defined as multiples of this one.
    public function multiples()
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    // Only units that aren't multiples themselves can be used as a base.
    public function scopeBaseUnits($query)
    {
        return $query->whereNull('base_unit_id');
    }

    // "1 Carton = 24 Piece (pc)", or null for a base unit.
    public function getConversionAttribute(): ?string
    {
        if (!$this->base_unit_id || !$this->baseUnit) {
            return null;
        }

        return "1 {$this->name} = {$this->multiplier} {$this->baseUnit->name} ({$this->baseUnit->short_name})";
    }
}
