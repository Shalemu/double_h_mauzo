<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = ['batch', 'recipient', 'receiver', 'type', 'message', 'sms_count', 'status', 'response', 'sent_by'];

    protected $casts = [
        'response' => 'array',
    ];

    public function sentBy()
    {
        return $this->belongsTo(Users::class, 'sent_by');
    }
}
