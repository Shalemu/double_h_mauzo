<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = ['from_shop_id', 'to_shop_id', 'transferred_by', 'note'];

    public function fromShop()
    {
        return $this->belongsTo(Shops::class, 'from_shop_id');
    }

    public function toShop()
    {
        return $this->belongsTo(Shops::class, 'to_shop_id');
    }

    public function transferredBy()
    {
        return $this->belongsTo(Users::class, 'transferred_by');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
