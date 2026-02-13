<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;

    // Fillable fields
    protected $fillable = [
        'shop_id',
        'title',
        'amount',
        'note',
        'receipt',
        'created_by_id',
        'created_by_type',
        'user_id',
        'staff_id',
    ];

    /**
     * The shop this expense belongs to.
     */
    public function shop()
    {
        return $this->belongsTo(Shops::class, 'shop_id');
    }

    /**
     * Polymorphic creator relationship.
     * Can still use if needed.
     */
    public function createdBy()
    {
        return $this->morphTo();
    }

    /**
     * User relation (if created by a user)
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\Users::class, 'user_id');
    }

    /**
     * Staff relation (if created by a staff member)
     */
    public function staff()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
    }

    /**
     * Get the creator name for display.
     */
    public function getCreatorNameAttribute()
    {
        if ($this->user) {
            return $this->user->full_name;
        }

        if ($this->staff) {
            return $this->staff->full_name;
        }

        return 'Unknown';
    }
}
