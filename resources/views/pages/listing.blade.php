@extends('layouts.app')

{{--
    صفحة فهرس عامة (placeholder خفيفة).
    متغيرات: $title, $subtitle, $breadcrumbs, $items, $itemListName
    $items: [['name' => .., 'url' => .., 'excerpt' => ..], ...]
--}}

@if (! empty($items))
    @push('schema')
        <x-schema.item-list :items="$items" :name="$itemListName ?? $title" />
    @endpush
@endif

@section('content')
    <x-page-hero :title="$title" :subtitle="$subtitle ?? null" :breadcrumbs="$breadcrumbs ?? []" />

    <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
        @if (! empty($items))
            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ $item['url'] }}"
                           class="group block h-full rounded-2xl border border-line bg-white p-6 hover:border-brand transition-colors">
                            <h2 class="text-lg font-bold mb-1.5 group-hover:text-brand transition-colors">{{ $item['name'] }}</h2>
                            @if (! empty($item['excerpt']))
                                <p class="text-muted text-sm leading-relaxed">{{ $item['excerpt'] }}</p>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            {{-- فاضي => "قريباً" (الصفحة دي noindex من الـ Controller) --}}
            <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-16 px-4">
                <p class="text-muted text-lg">المحتوى قريباً — بنجهّز القسم ده.</p>
                <a href="{{ url('/') }}#booking"
                   class="inline-block mt-5 px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                    احجز فني دلوقتي
                </a>
            </div>
        @endif
    </section>
@endsection
