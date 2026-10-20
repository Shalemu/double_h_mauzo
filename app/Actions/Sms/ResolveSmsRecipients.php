<?php

namespace App\Actions\Sms;

use App\DTO\Sms\SmsMessageData;
use App\Enums\SmsAudience;
use App\Helpers\Phone;
use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Support\Collection;

/**
 * Turn "Send to" choices into a de-duplicated list of valid phone numbers.
 */
class ResolveSmsRecipients
{
    /**
     * @return array{0: Collection, 1: string[]} recipients [phone, name, type], invalid numbers
     */
    public function execute(SmsMessageData $data): array
    {
        $type = $data->audience->logType();
        $list = collect();

        if ($data->audience === SmsAudience::Custom) {
            foreach (preg_split('/[\s,;]+/', $data->customNumbers, -1, PREG_SPLIT_NO_EMPTY) as $raw) {
                $list->push(['phone' => $raw, 'name' => null, 'type' => $type]);
            }
        } else {
            $isStaff = $data->audience === SmsAudience::Staff;
            $query = ($isStaff ? Staff::query() : Customer::query())
                ->whereNotNull('phone')->where('phone', '!=', '')
                ->when($data->shopId, fn ($q, $shop) => $q->where('shop_id', $shop))
                ->when($data->onlySelected, fn ($q) => $q->whereIn('id', $data->ids ?: [0]));

            foreach ($query->get() as $person) {
                $list->push([
                    'phone' => $person->phone,
                    'name' => $isStaff ? trim($person->first_name . ' ' . $person->last_name) : $person->name,
                    'type' => $type,
                ]);
            }
        }

        $invalid = [];
        $valid = $list->map(function ($r) use (&$invalid) {
            $r['phone'] = Phone::format($r['phone']);
            if (!Phone::isValid($r['phone'])) {
                $invalid[] = $r['name'] ? "{$r['name']} ({$r['phone']})" : $r['phone'];

                return null;
            }

            return $r;
        })->filter()->unique('phone')->values();

        return [$valid, $invalid];
    }
}
