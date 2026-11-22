{{-- رابط داخلي للصفحة الأساسية للكلمة الأهم (من الإعدادات). بيتخفى لو النص/الرابط فاضي أو لو إحنا على نفس الصفحة. --}}
@php
    $hubText = trim((string) site('hub_link_text'));
    $hubUrl = trim((string) site('hub_link_url'));
    $hubOk = $hubText !== '' && str_starts_with($hubUrl, '/') && ! str_starts_with($hubUrl, '//')
        && '/' . ltrim(request()->path(), '/') !== $hubUrl;
@endphp
@if ($hubOk)
    <p class="mt-6 text-muted leading-relaxed">
        {{ site('hub_link_lead') }}
        <a href="{{ $hubUrl }}" class="text-brand font-bold underline underline-offset-4 hover:no-underline">{{ $hubText }}</a>
    </p>
@endif
