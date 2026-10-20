<?php

namespace App\DTO\Sms;

use App\DTO\DataTransferObject;
use App\Enums\SmsAudience;

/**
 * One "Send SMS" from the Compose page: who it goes to and the text.
 */
class SmsMessageData extends DataTransferObject
{
    /**
     * @param int[] $ids chosen customers/staff when $onlySelected
     */
    public function __construct(
        public readonly SmsAudience $audience,
        public readonly string $message,
        public readonly bool $onlySelected = false,
        public readonly ?int $shopId = null,
        public readonly array $ids = [],
        public readonly string $customNumbers = '',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            audience: SmsAudience::from($data['audience']),
            message: (string) $data['message'],
            onlySelected: ($data['scope'] ?? 'all') === 'selected',
            shopId: !empty($data['shop_id']) ? (int) $data['shop_id'] : null,
            ids: array_map('intval', $data['ids'] ?? []),
            customNumbers: (string) ($data['custom_numbers'] ?? ''),
        );
    }
}
