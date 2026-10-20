<?php

namespace App\DTO;

/**
 * Base for typed request payloads. Each DTO builds itself from validated
 * input (fromRequest / fromArray) so Actions receive clean, typed data
 * instead of raw request arrays.
 */
abstract class DataTransferObject
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
