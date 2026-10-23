<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Read-only review list of duplicate products: the same name more than once
 * in one shop/store (mostly from importing the same sheet repeatedly). For
 * each group it suggests which copy to keep — the one with the most sales /
 * purchase / transfer history — and what its stock should be. Nothing is
 * changed; the merge is a separate, approved step.
 */
class ProductDuplicatesReport extends Command
{
    protected $signature = 'products:duplicates-report {--shop= : Only this shop/store id}';

    protected $description = 'Write an Excel review list of duplicate products (same name in the same shop). Changes nothing.';

    /** Tables pointing at a product, as label => [table, column]. */
    public const REFERENCES = [
        'Sale lines'         => ['sale_items', 'product_id'],
        'Sale returns'       => ['sale_returns', 'product_id'],
        'Purchase lines'     => ['purchase_items', 'product_id'],
        'Purchase returns'   => ['purchase_returns', 'product_id'],
        'Order lines'        => ['order_items', 'product_id'],
        'Transfers out'      => ['stock_transfer_items', 'from_product_id'],
        'Transfers in'       => ['stock_transfer_items', 'to_product_id'],
        'Distribution lines' => ['distribution_items', 'product_id'],
        'Supplier links'     => ['supplier_products', 'product_id'],
        'Cart lines'         => ['carts', 'product_id'],
    ];

    public function handle()
    {
        // The copies sheet can run to tens of thousands of rows; PhpSpreadsheet holds it all in memory.
        ini_set('memory_limit', '2048M');

        $products = DB::table('products')
            ->leftJoin('shops', 'shops.id', '=', 'products.shop_id')
            ->whereNull('products.deleted_at')
            ->when($this->option('shop'), fn ($q, $shop) => $q->where('products.shop_id', $shop))
            ->orderBy('products.shop_id')->orderBy('products.name')->orderBy('products.id')
            ->get(['products.id', 'products.name', 'products.shop_id', 'shops.name as shop', 'products.barcode',
                'products.quantity', 'products.purchase_price', 'products.selling_price', 'products.wholesale_price',
                'products.created_at']);

        // Grouped here rather than in SQL: a grouped subquery over every product is very slow in MySQL.
        $groups = $products->groupBy(fn ($p) => $p->shop_id . '|' . mb_strtolower(trim($p->name)))
            ->filter(fn ($copies) => $copies->count() > 1);

        if ($groups->isEmpty()) {
            $this->info('No duplicate products found.');
            return 0;
        }

        $products = $groups->flatten(1);
        $refs = $this->referenceCounts($products->pluck('id'));

        $summary = [];
        $detail = [];
        foreach ($groups as $copies) {
            $plan = self::plan($copies, $refs);
            $first = $copies->first();
            $summary[] = [
                $first->shop, $first->name, $copies->count(), $plan['keep']->id,
                $copies->where('id', '!=', $plan['keep']->id)->pluck('id')->implode(', '),
                $plan['imported'], $plan['movement'], $plan['stock'], $copies->sum('quantity'),
                $plan['history'], implode('; ', $plan['warnings']),
            ];
            foreach ($copies as $p) {
                $detail[] = array_merge(
                    [$p->shop, $p->name, $p->id, $p->id === $plan['keep']->id ? 'KEEP' : 'merge into ' . $plan['keep']->id,
                        $p->barcode, $p->quantity, $p->purchase_price, $p->selling_price, $p->wholesale_price, $p->created_at],
                    array_map(fn ($label) => $refs[$p->id][$label] ?? 0, array_keys(self::REFERENCES))
                );
            }
        }

        $path = 'reports/product-duplicates-' . now()->format('Y-m-d-His') . '.xlsx';
        Excel::store(new class($summary, $detail) implements WithMultipleSheets {
            public function __construct(private array $summary, private array $detail) {}

            public function sheets(): array
            {
                $sheet = fn ($title, $headings, $rows) => new class($title, $headings, $rows) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStrictNullComparison {
                    public function __construct(private $title, private $headings, private $rows) {}
                    public function array(): array { return $this->rows; }
                    public function headings(): array { return $this->headings; }
                    public function title(): string { return $this->title; }
                };

                return [
                    $sheet('Groups', ['Shop / Store', 'Product', 'Copies', 'Keep id', 'Merge ids', 'Imported qty',
                        'Net movement on copies', 'Suggested stock', 'Sum of all copies (wrong)', 'History lines', 'Check'], $this->summary),
                    $sheet('Copies', array_merge(['Shop / Store', 'Product', 'Id', 'Action', 'Barcode', 'Qty', 'Buy', 'Retail',
                        'Wholesale', 'Created'], array_keys(ProductDuplicatesReport::REFERENCES)), $this->detail),
                ];
            }
        }, $path, 'local');

        $copies = $products->count();
        $this->info("{$groups->count()} duplicate groups, {$copies} product rows (" . ($copies - $groups->count()) . ' would be merged away).');
        $this->info('Review list: ' . storage_path('app/' . $path));

        return 0;
    }

    /** product id => [reference label => count] */
    public static function referenceCounts(Collection $ids): array
    {
        $counts = [];
        foreach ($ids->chunk(5000) as $chunk) {
            foreach (self::REFERENCES as $label => [$table, $column]) {
                $rows = DB::table($table)->whereIn($column, $chunk)->groupBy($column)
                    ->selectRaw("$column as id, count(*) as c")->pluck('c', 'id');
                foreach ($rows as $id => $c) {
                    $counts[$id][$label] = $c;
                }
            }
        }

        return $counts;
    }

    /**
     * Which copy to keep and its stock. Repeated imports give every copy the
     * same starting quantity, so the most common quantity is the imported
     * count; each copy's difference from it is what moved on that copy
     * (sales, purchases, transfers). Suggested stock = imported + all moves.
     */
    public static function plan(Collection $copies, array $refs): array
    {
        $history = fn ($p) => array_sum(array_diff_key($refs[$p->id] ?? [], ['Supplier links' => 1]));
        $keep = $copies->sort(fn ($a, $b) => [$history($b), $a->id] <=> [$history($a), $b->id])->first();

        $imported = (float) $copies->countBy(fn ($p) => (string) (float) $p->quantity)->sortDesc()->keys()->first();
        $movement = $copies->sum(fn ($p) => (float) $p->quantity - $imported);

        $warnings = [];
        if ($copies->pluck('barcode')->filter()->unique()->count() > 1) {
            $warnings[] = 'different barcodes — may be different products';
        }
        foreach (['purchase_price' => 'buying', 'selling_price' => 'retail', 'wholesale_price' => 'wholesale'] as $column => $label) {
            if ($copies->pluck($column)->map(fn ($v) => (float) $v)->unique()->count() > 1) {
                $warnings[] = "{$label} prices differ";
            }
        }
        if ($copies->countBy(fn ($p) => (string) (float) $p->quantity)->max() < 2) {
            $warnings[] = 'no common starting quantity — check stock by hand';
        }

        return [
            'keep' => $keep,
            'imported' => $imported,
            'movement' => $movement,
            'stock' => $imported + $movement,
            'history' => $copies->sum($history),
            'warnings' => $warnings,
        ];
    }
}
