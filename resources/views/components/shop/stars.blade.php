@props([
    'value' => 0,     // متوسط التقييم (0..5)
    'size' => 'h-4 w-4',
])
@php
    $full = (int) floor($value);
    $hasHalf = ($value - $full) >= 0.5;
@endphp
<span class="inline-flex items-center gap-0.5 text-amber-400" aria-hidden="true">
    @for ($i = 1; $i <= 5; $i++)
        @php $fill = $i <= $full ? 'currentColor' : (($i === $full + 1 && $hasHalf) ? 'url(#half)' : 'none'); @endphp
        <svg viewBox="0 0 24 24" class="{{ $size }}" fill="{{ $fill }}" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            @if ($i === 1)
                <defs><linearGradient id="half"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="transparent"/></linearGradient></defs>
            @endif
            <path d="M12 2l2.9 6.3 6.6.6-5 4.4 1.5 6.5L12 17.8 6 20.3l1.5-6.5-5-4.4 6.6-.6L12 2z"/>
        </svg>
    @endfor
</span>
