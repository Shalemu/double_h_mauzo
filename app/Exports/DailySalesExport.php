<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailySalesExport implements FromArray, WithHeadings
{
    protected $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return array_map(function($row, $index) {
            return [
                $index + 1,
                $row['product'],
                $row['quantity'],
                $row['revenue'],
                $row['staff'],
                'View Report', // Placeholder for Action
            ];
        }, $this->rows, array_keys($this->rows));
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Item',
            'Quantity Sold',
            'Total (TZS)',
            'Sold by',
            'Action'
        ];
    }
}
