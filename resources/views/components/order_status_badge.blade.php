@php
    $badge = [
        'pending'   => ['bg-warning text-dark', 'Pending'],
        'approved'  => ['bg-primary', 'Approved'],
        'purchased' => ['bg-success', 'Purchased'],
        'rejected'  => ['bg-danger', 'Rejected'],
    ][$status] ?? ['bg-secondary', ucfirst($status)];
@endphp
<span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
