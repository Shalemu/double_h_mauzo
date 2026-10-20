<?php

namespace App\Exports;

use App\DataTables\ProductsExportTable;
use App\Exports\Writers\SimpleXlsx;

/**
 * Products → .xlsx, streamed row by row (SimpleXlsx) because building the
 * whole sheet in memory timed out on shops with tens of thousands of products.
 */
class ProductsExcelExport
{
    private const HEADINGS = [
        'Name', 'Shop', 'Brand', 'Category', 'Unit', 'Quantity', 'Min Quantity',
        'Purchase Price', 'Selling Price', 'Wholesale Price', 'Barcode', 'Expire Date', 'Size', 'Color',
    ];

    public function __construct(private ProductsExportTable $table)
    {
    }

    public function download()
    {
        set_time_limit(300);
        $xlsx = new SimpleXlsx(self::HEADINGS);

        foreach ($this->table->query()->cursor() as $p) {
            $xlsx->addRow([
                $p->name, $p->shop_name, $p->brand, $p->category_name, $p->unit_name,
                (int) $p->quantity, (int) $p->min_quantity,
                (float) $p->purchase_price, (float) $p->selling_price, (float) $p->wholesale_price,
                $p->barcode !== null ? (string) $p->barcode : null,
                $p->expire_date, $p->size, $p->color,
            ]);
        }

        return response()->download($xlsx->save(), 'products_' . $this->table->label() . '_' . now()->format('Y-m-d') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
