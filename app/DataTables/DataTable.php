<?php

namespace App\DataTables;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * A list page's data: its base query, filters/search from the request, and
 * pagination. Controllers call ->paginate() and pass the result to the view,
 * which renders the table as before (server-side).
 */
abstract class DataTable
{
    protected int $perPage = 10;

    /** Query-string name of the page parameter (pages with two lists need different ones). */
    protected string $pageName = 'page';

    public function __construct(protected Request $request)
    {
    }

    public static function make(Request $request): static
    {
        return new static($request);
    }

    /** Base query with filters/search applied. */
    abstract public function query();

    public function paginate(?int $perPage = null): LengthAwarePaginator
    {
        return $this->query()
            ->paginate($perPage ?? $this->perPage, ['*'], $this->pageName)
            ->withQueryString();
    }

    protected function search(): string
    {
        return trim((string) $this->request->query('q'));
    }
}
