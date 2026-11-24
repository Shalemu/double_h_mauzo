<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * A financial year (Settings › Financial Years). One is current: the year
 * the business is operating in, shown in the sidebar and offered as a date
 * range on the reports.
 */
class FinancialYear extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'is_current'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /** Remembered for the request: the sidebar and report filters both ask. */
    private static $current = false;

    public static function current(): ?self
    {
        if (self::$current === false) {
            self::$current = Schema::hasTable('financial_years')
                ? static::where('is_current', true)->first()
                : null;
        }

        return self::$current;
    }

    /** For report filters: newest first. */
    public static function forFilters()
    {
        return Schema::hasTable('financial_years')
            ? static::orderByDesc('start_date')->get()
            : collect();
    }

    public function contains($date): bool
    {
        $date = \Carbon\Carbon::parse($date)->startOfDay();

        return $date->betweenIncluded($this->start_date, $this->end_date);
    }

    /** "1 Jan 2026 – 31 Dec 2026" */
    public function period(): string
    {
        return $this->start_date->format('j M Y') . ' – ' . $this->end_date->format('j M Y');
    }

    /** Make this the only current year. */
    public function makeCurrent(): void
    {
        static::where('id', '!=', $this->id)->where('is_current', true)->update(['is_current' => false]);
        $this->update(['is_current' => true]);
        self::$current = false;
    }
}
