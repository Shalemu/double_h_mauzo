<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionPayment extends Model
{
    protected $fillable = ['distribution_id', 'amount', 'received_by', 'note'];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(Users::class, 'received_by');
    }
}
