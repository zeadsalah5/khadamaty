@props(['variant' => 'dark'])

@php
    // dark = نص كحلي (للهيدر الأبيض) | light = نص أبيض (للفوتر/الهيرو)
    $text = $variant === 'light' ? '#ffffff' : 'var(--color-navy)';
    $sub  = $variant === 'light' ? 'rgba(255,255,255,.75)' : 'var(--color-muted)';
@endphp

<a href="{{ url('/') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 group']) }}
   aria-label="{{ site('name') }} — الصفحة الرئيسية">
    {{-- الرمز: وحدة تكييف مبسطة مع موجات هواء بارد (SVG) --}}
    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"
         class="shrink-0 transition-transform duration-200 group-hover:-translate-y-0.5" aria-hidden="true">
        <rect x="3" y="7" width="34" height="15" rx="4" fill="var(--color-brand)"/>
        <rect x="7" y="12" width="26" height="2.2" rx="1.1" fill="#fff" opacity=".9"/>
        <rect x="7" y="16" width="26" height="2.2" rx="1.1" fill="#fff" opacity=".55"/>
        <path d="M11 27c0 2.5 2 2.5 2 5" stroke="var(--color-accent)" stroke-width="2.4" stroke-linecap="round"/>
        <path d="M20 27c0 2.5 2 2.5 2 5" stroke="var(--color-accent)" stroke-width="2.4" stroke-linecap="round"/>
        <path d="M29 27c0 2.5 2 2.5 2 5" stroke="var(--color-accent)" stroke-width="2.4" stroke-linecap="round"/>
    </svg>

    {{-- الاسم كنص --}}
    <span class="leading-none">
        <span class="block text-lg font-extrabold tracking-tight" style="color: {{ $text }}">
            {{ site('name') }}
        </span>
        <span class="block text-[11px] font-medium mt-0.5" style="color: {{ $sub }}">
            {{ site('tagline') }}
        </span>
    </span>
</a>
