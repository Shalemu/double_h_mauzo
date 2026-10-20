<?php

namespace App\Actions\Sms;

use App\DTO\Sms\SmsMessageData;
use App\Enums\SmsStatus;
use App\Models\SmsLog;
use App\Services\SMSService;
use Illuminate\Support\Str;

/**
 * Send one message to every recipient (personalised with {name}) through
 * Mobishastra, logging each attempt.
 */
class SendBulkSms
{
    public function __construct(private SMSService $sms, private ResolveSmsRecipients $resolve)
    {
    }

    /**
     * @return array{total: int, sent: int, failed: string[], invalid: string[]}
     */
    public function execute(SmsMessageData $data, ?int $sentBy = null): array
    {
        [$recipients, $invalid] = $this->resolve->execute($data);

        set_time_limit(max(60, $recipients->count() * 5));
        $batch = (string) Str::uuid();
        $sent = 0;
        $failed = [];

        foreach ($recipients as $r) {
            $text = self::personalise($data->message, $r['name']);
            $result = $this->sms->sendSMS($r['phone'], $text);

            SmsLog::create([
                'batch' => $batch,
                'recipient' => $r['phone'],
                'receiver' => $r['name'],
                'type' => $r['type'],
                'message' => $text,
                'sms_count' => SMSService::partsFor($text),
                'status' => ($result['status'] ? SmsStatus::Sent : SmsStatus::Failed)->value,
                'response' => ['body' => Str::limit($result['response'], 500), 'error_type' => $result['error_type']],
                'sent_by' => $sentBy,
            ]);

            if ($result['status']) {
                $sent++;
            } else {
                $failed[] = ($r['name'] ?: $r['phone']) . ($result['error_type'] ? " ({$result['error_type']})" : '');
            }
        }

        return ['total' => $recipients->count(), 'sent' => $sent, 'failed' => $failed, 'invalid' => $invalid];
    }

    /** {name} → the recipient's first name ("Customer" when unknown). */
    public static function personalise(string $message, ?string $name): string
    {
        return str_replace('{name}', $name ? (string) Str::of($name)->before(' ')->title() : 'Customer', $message);
    }
}
