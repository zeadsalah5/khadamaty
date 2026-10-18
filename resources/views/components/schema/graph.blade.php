@props(['data' => []])
@php
    // بنشيل المفاتيح الفاضية علشان مايطلعش حقول بلا قيمة.
    $clean = collect($data)->filter(fn ($v) => $v !== null && $v !== '' && $v !== [])->all();
@endphp
@if (! empty($clean))
<script type="application/ld+json">
{!! json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
