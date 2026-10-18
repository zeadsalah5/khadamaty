@extends('layouts.app')

{{--
    صفحة عنصر مفرد (placeholder خفيفة).
    متغيرات: $title, $subtitle, $breadcrumbs, $type, $model, $canonical
    $type: service|post|product|area|appliance|brand|vendor — بيحدد نوع الـ schema.
--}}

@push('schema')
    @switch($type)
        @case('service')
            <x-schema.service :name="$title" :description="$subtitle" :url="$canonical" />
            @break
        @case('post')
            <x-schema.article
                :headline="$title"
                :description="$subtitle"
                :url="$canonical"
                :datePublished="optional($model->published_at)->toIso8601String()"
                :dateModified="optional($model->updated_at)->toIso8601String()" />
            @break
        @case('product')
            <x-schema.product
                :name="$title"
                :description="$subtitle"
                :url="$canonical"
                :sku="$model->sku"
                :price="$model->price" />
            @break
    @endswitch
@endpush

@section('content')
    <x-page-hero :title="$title" :subtitle="$subtitle ?? null" :breadcrumbs="$breadcrumbs ?? []" />

    <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
        @php $body = $model->body ?? null; @endphp
        @if (! empty($body))
            <div class="prose max-w-3xl leading-relaxed text-ink/90">{!! nl2br(e($body)) !!}</div>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-16 px-4">
                <p class="text-muted text-lg">تفاصيل أكتر عن «{{ $title }}» قريباً.</p>
                <a href="{{ url('/') }}#booking"
                   class="inline-block mt-5 px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                    احجز الخدمة دلوقتي
                </a>
            </div>
        @endif
    </section>
@endsection
