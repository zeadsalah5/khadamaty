@props(['brand', 'variant' => 'box'])

{{--
    توضيح الاستقلال (إلزامي لصفحات ماركة × خدمة — K1).
    مركز صيانة مستقل، غير تابع للماركة ولا وكيل معتمد. من غير لوجو رسمي ولا
    "خدمة عملاء" ولا "رقم الوكيل". variant: box (كارت) | plain (نص بسيط للفوتر).
--}}

@php
    $text = 'مركز صيانة مستقل، غير تابع لشركة ' . $brand . ' ولا وكيل معتمد لها.';
    $warranty = 'لو جهازك تحت الضمان، تواصل مع الوكيل الرسمي. لو خارج الضمان أو محتاج فني سريع، إحنا نخدمك.';
@endphp

@if ($variant === 'plain')
    <p {{ $attributes->merge(['class' => 'text-sm text-muted leading-relaxed']) }}>
        {{ $text }} {{ $warranty }}
    </p>
@else
    <div {{ $attributes->merge(['class' => 'inline-flex items-start gap-2 rounded-xl bg-surface border border-line px-4 py-3 text-sm text-muted leading-relaxed max-w-2xl']) }}>
        <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-brand mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <span><strong class="font-semibold text-ink">{{ $text }}</strong> {{ $warranty }}</span>
    </div>
@endif
