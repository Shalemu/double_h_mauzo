<?php

namespace App\Exports;

use App\DataTables\ProductsExportTable;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Products → PDF. Long lists are refused: DomPDF can't lay out tens of
 * thousands of rows in a web request — Excel is offered instead.
 */
class ProductsPdfExport
{
    /** Above this many rows the PDF is too slow/large to build. */
    public const ROW_LIMIT = 2000;

    public function __construct(private ProductsExportTable $table)
    {
    }

    public function count(): int
    {
        return (clone $this->table->query())->count();
    }

    public function tooLarge(): bool
    {
        return $this->count() > self::ROW_LIMIT;
    }

    public function download()
    {
        // DomPDF needs far more than the default 128MB for long tables.
        set_time_limit(180);
        ini_set('memory_limit', '768M');

        $pdf = Pdf::loadView('pages.products.pdf', [
            'products' => $this->table->query()->get(),
            'shopName' => $this->table->shopName(),
            'filter' => $this->table->filter()->value,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('products_' . $this->table->label() . '_' . now()->format('Y-m-d') . '.pdf');
    }
}
