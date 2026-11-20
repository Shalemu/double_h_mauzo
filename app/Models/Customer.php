<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'date_of_birth', 'email', 'address', 'shop_id'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function sales()
    {
        return $this->hasMany(\App\Models\Sale::class);
    }

    public function shop()
    {
        return $this->belongsTo(\App\Models\Shops::class, 'shop_id');
    }

    /**
     * Customers whose birthday falls on $date (for birthday SMS wishes).
     * In a non-leap year, people born on 29 Feb are greeted on 28 Feb.
     */
    public function scopeBirthdayOn($query, CarbonInterface $date)
    {
        return $query->whereNotNull('date_of_birth')->where(function ($q) use ($date) {
            $q->where(fn ($d) => $d->whereMonth('date_of_birth', $date->month)->whereDay('date_of_birth', $date->day));

            if ($date->month === 2 && $date->day === 28 && !$date->isLeapYear()) {
                $q->orWhere(fn ($d) => $d->whereMonth('date_of_birth', 2)->whereDay('date_of_birth', 29));
            }
        });
    }
}
