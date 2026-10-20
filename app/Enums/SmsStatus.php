<?php

namespace App\Enums;

/**
 * sms_logs.status.
 */
enum SmsStatus: string
{
    case Sent = 'sent';
    case Failed = 'failed';
}
