<?php

namespace App\DataTables;

use App\Enums\DistributionStatus;
use App\Models\Distribution;

/**
 * Distribution list: filter by store and by status (Still out / Settled /
 * Owing money).
 */
class DistributionsDataTable extends DataTable
{
    public function storeId(): ?string
    {
        return $this->request->query('store') ?: null;
    }

    /** 'out', 'settled', 'owing' or null. */
    public function status(): ?string
    {
        $status = $this->request->query('status');

        return in_array($status, ['out', 'settled', 'owing'], true) ? $status : null;
    }

    public function query()
    {
        $status = $this->status();

        return Distribution::with('store', 'staff', 'issuedBy', 'items')
            ->when($this->storeId(), fn ($q, $store) => $q->where('store_id', $store))
            ->when($status === 'out', fn ($q) => $q->where('status', DistributionStatus::Out->value))
            ->when($status === 'settled', fn ($q) => $q->where('status', DistributionStatus::Settled->value))
            ->when($status === 'owing', fn ($q) => $q->where('balance', '>', 0))
            ->latest();
    }
}
