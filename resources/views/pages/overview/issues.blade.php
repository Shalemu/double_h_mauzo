@extends('main')

@section('title', 'Reported Issues')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-circle me-2"></i>Reported Issues</h5>
            <small class="text-muted">Problems staff reported from their shops.</small>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-3 gap-1">
                @foreach(['open' => 'Open', 'resolved' => 'Resolved', 'all' => 'All'] as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.issues', ['status' => $key, 'shop' => $shopId]) }}">
                            {{ $label }}
                            @if($key !== 'all')
                                <span class="badge {{ $status === $key ? 'bg-light text-dark' : 'bg-secondary' }} ms-1">{{ (int) ($counts->$key ?? 0) }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            @include('pages.overview._shop_filter', ['keep' => ['status' => $status]])

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>Reported</th><th>Shop</th><th>By</th><th>Type</th><th>Message</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($issues as $issue)
                            <tr>
                                <td class="text-nowrap">{{ $issue->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $issue->shop->name ?? '—' }}</td>
                                <td>{{ $issue->staff->full_name ?? '—' }}</td>
                                <td>{{ ucfirst($issue->type) }}</td>
                                <td style="max-width:420px">{{ $issue->message }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $issue->status === 'resolved' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($issue->status ?? 'pending') }}</span>
                                </td>
                                <td class="text-center">
                                    @if($issue->status !== 'resolved')
                                        <form method="POST" action="{{ route('admin.feedback.resolve', $issue->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-success"><i class="bi bi-check2"></i> Resolve</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No {{ $status === 'all' ? '' : $status }} issues.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $issues->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>
@endsection
