<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'title',
        'amount',
        'note',
    ];

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }
}
