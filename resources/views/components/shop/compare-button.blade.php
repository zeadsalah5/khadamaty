@props([
    'product',
    'variant' => 'icon',
])
@php
    $inList = \App\Support\Store\Compare::has($product->id);
@endphp

<form method="POST" action="{{ route('compare.add') }}" class="contents">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">

    @if ($variant === 'full')
        <button type="submit"
                aria-pressed="{{ $inList ? 'true' : 'false' }}"
                class="inline-flex items-center gap-2 rounded-full border px-6 py-3 font-bold transition-colors {{ $inList ? 'border-navy bg-navy/5 text-navy' : 'border-line hover:border-navy hover:text-navy' }}">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/><path d="M7 3v6M17 15v6"/></svg>
            {{ $inList ? 'في المقارنة' : 'قارن' }}
        </button>
    @else
        <button type="submit"
                aria-label="{{ $inList ? 'شيل من المقارنة' : 'أضف للمقارنة' }}"
                aria-pressed="{{ $inList ? 'true' : 'false' }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border bg-white/90 backdrop-blur transition-colors {{ $inList ? 'border-navy text-navy' : 'border-line text-ink hover:border-navy hover:text-navy' }}">
            <svg viewBox="0 0 24 24" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/><path d="M7 3v6M17 15v6"/></svg>
        </button>
    @endif
</form>
