<li class="nav-item mb-1">
    <a href="{{ route($route, $params) }}" class="nav-link d-flex align-items-center px-3 py-2 {{ $isActive ? 'active' : '' }}"
       @if($isActive) aria-current="page" @endif>
        <i class="nav-icon bi {{ $icon }}"></i><span>{{ $label }}</span>
        @if($badge)
            <span class="nav-badge" aria-label="{{ $badge }} pending">{{ $badge }}</span>
        @endif
    </a>
</li>
