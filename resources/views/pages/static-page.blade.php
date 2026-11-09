@extends('layouts.app')

{{--
    صفحة ثابتة (سياسات/تعريفية) — العنوان + متن Markdown من الداشبورد.
    متغيرات: $page, $content, $breadcrumbs
--}}

@section('content')
    <x-page-hero :title="$page->title" :breadcrumbs="$breadcrumbs" />

    <section class="mx-auto max-w-3xl px-4 py-10 md:py-14">
        @if (filled($content->html))
            <div class="prose prose-lg max-w-none text-ink/90 leading-loose">
                {!! $content->html !!}
            </div>
        @else
            <p class="text-muted">المحتوى هيتضاف قريباً.</p>
        @endif
    </section>
@endsection
