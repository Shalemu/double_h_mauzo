<?php

namespace App\DataTables;

use App\Enums\SmsStatus;
use App\Models\SmsLog;

/**
 * SMS → History: every message sent, filterable by status and searchable
 * by name, number or text.
 */
class SmsLogsDataTable extends DataTable
{
    protected int $perPage = 15;

    public function status(): ?SmsStatus
    {
        return SmsStatus::tryFrom((string) $this->request->query('status'));
    }

    public function query()
    {
        $search = $this->search();

        return SmsLog::with('sentBy:id,name')
            ->when($this->status(), fn ($q, $status) => $q->where('status', $status->value))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('recipient', 'like', "%{$search}%")
                ->orWhere('receiver', 'like', "%{$search}%")->orWhere('message', 'like', "%{$search}%")))
            ->latest();
    }
}
