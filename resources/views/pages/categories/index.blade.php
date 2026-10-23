@extends('main')

@section('title', 'Categories')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Categories</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Products</a>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" data-mode="create">
                    <i class="bi bi-plus-lg"></i> New Category
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-2 mb-3" style="max-width:420px">
                <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search categories..." aria-label="Search categories">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th class="text-start">Category</th><th class="text-start">Description</th><th>Products</th><th style="width:120px">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            @foreach([$category, ...$category->children] as $row)
                                @php
                                    $isChild = $row->parent_id !== null;
                                    $count = $isChild ? $row->subcategory_products_count : $row->products_count;
                                @endphp
                                <tr class="{{ $isChild ? '' : 'table-light' }}">
                                    <td class="{{ $isChild ? 'ps-5' : 'fw-semibold' }}">
                                        @if($isChild)<i class="bi bi-arrow-return-right text-muted me-1"></i>@endif
                                        {{ $row->name }}
                                    </td>
                                    <td>{{ $row->description ?: '—' }}</td>
                                    <td class="text-center">{{ number_format($count ?? 0) }}</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#categoryModal"
                                                data-mode="edit" data-action="{{ route('categories.update', $row->id) }}"
                                                data-name="{{ $row->name }}" data-description="{{ $row->description }}"
                                                data-parent="{{ $row->parent_id }}" data-id="{{ $row->id }}"
                                                aria-label="Edit {{ $row->name }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('categories.destroy', $row->id) }}" class="d-inline"
                                              onsubmit="return confirm('Delete category {{ addslashes($row->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $row->name }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No categories found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">@if($categories->total()) Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ number_format($categories->total()) }} main categories @endif</small>
                {{ $categories->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('categories.store') }}" data-store="{{ route('categories.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="categoryName">Name</label>
                    <input type="text" name="name" id="categoryName" class="form-control" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="categoryParent">Parent category</label>
                    <select name="parent_id" id="categoryParent" class="form-select">
                        <option value="">None — this is a main category</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pick a parent to make this a subcategory.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="categoryDescription">Description (optional)</label>
                    <textarea name="description" id="categoryDescription" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

@include('pages.categories._index_scripts')
@endsection
