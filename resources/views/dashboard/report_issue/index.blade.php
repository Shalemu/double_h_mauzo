
<div class="container-fluid mt-4">

    <div class="card shadow-sm w-100">

    <h3>{{ $shop->name }} - Dashboard</h3>

    {{-- Other shop info (products, sales, etc.) --}}

    
    <div class="card mt-4 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Staff Reported Issues</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Reported By</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse($feedbacks as $feedback)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $feedback->type }}</td>
                            <td>{{ $feedback->message }}</td>
                            <td>
                                <span class="badge {{ $feedback->status === 'resolved' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($feedback->status) }}
                                </span>
                            </td>
                           <td>{{ $feedback->staff->full_name ?? 'N/A' }}</td>
                            <td>{{ $feedback->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($feedback->status !== 'resolved')
                                    <form action="{{ route('admin.feedback.resolve', $feedback->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-success">Mark as Resolved</button>
                                    </form>
                                @else
                                    <span class="text-success">Resolved</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted">No issues reported yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
