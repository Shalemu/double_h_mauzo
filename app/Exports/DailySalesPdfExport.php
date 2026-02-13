<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

class DailySalesPdfExport
{
    protected $rows;
    protected $date;
    protected $shop;

    public function __construct($shop, $date, $rows)
    {
        $this->shop = $shop;
        $this->date = $date;
        $this->rows = $rows;
    }

    public function download($filename = 'daily_sales.pdf')
    {
        $pdf = Pdf::loadView('dashboard.sales.detail-pdf', [
            'shop' => $this->shop,
            'date' => $this->date,
            'itemRows' => $this->rows
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
