@extends('layouts.app')

{{--
    هب المدونة + صفحة التصنيف (/blog و /blog/category/{slug}) — المرحلة 5.
    متغيرات: $title, $subtitle, $posts (paginator), $categories, $activeCat,
             $items, $breadcrumbs
--}}

@if (! empty($items))
    @push('schema')
        <x-schema.item-list :items="$items" :name="$title" />
    @endpush
@endif

@section('content')
    <x-page-hero :title="$title" :subtitle="$subtitle ?? null" :breadcrumbs="$breadcrumbs ?? []" />

    {{-- ============ تنقّل التصنيفات ============ --}}
    @if ($categories->isNotEmpty())
        <nav class="mx-auto max-w-6xl px-4 pb-8" aria-label="تصنيفات المدونة">
            <ul class="flex flex-wrap gap-2.5">
                <li>
                    <a href="{{ route('blog.index') }}"
                       @class([
                           'inline-block rounded-full px-4 py-2 text-sm font-semibold border transition-colors',
                           'bg-brand text-white border-brand' => is_null($activeCat),
                           'bg-surface border-line hover:border-brand hover:text-brand' => ! is_null($activeCat),
                       ])
                       @if (is_null($activeCat)) aria-current="page" @endif>
                        كل المقالات
                    </a>
                </li>
                @foreach ($categories as $cat)
                    @continue($cat->published_count === 0 && optional($activeCat)->id !== $cat->id)
                    <li>
                        <a href="{{ route('blog.category', $cat->slug) }}"
                           @class([
                               'inline-block rounded-full px-4 py-2 text-sm font-semibold border transition-colors',
                               'bg-brand text-white border-brand' => optional($activeCat)->id === $cat->id,
                               'bg-surface border-line hover:border-brand hover:text-brand' => optional($activeCat)->id !== $cat->id,
                           ])
                           @if (optional($activeCat)->id === $cat->id) aria-current="page" @endif>
                            {{ $cat->name_ar }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif

    {{-- ============ شبكة المقالات ============ --}}
    <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
        @if ($posts->isNotEmpty())
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <li>
                        <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-white hover:border-brand hover:shadow-lg hover:shadow-brand/5 transition-all">
                            <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                <div class="aspect-video bg-surface overflow-hidden">
                                    @if (filled($post->cover))
                                        <x-img :src="'images/blog/' . $post->cover" :alt="$post->title_ar"
                                               :width="640" :height="360"
                                               class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                    @else
                                        {{-- مكان الصورة: placeholder لحد ما صاحب الموقع يرفع صورة (TODO) --}}
                                        <div class="w-full h-full flex items-center justify-center text-muted" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" class="w-12 h-12 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                                        </div>
                                    @endif
                                </div>
                            </a>
                            <div class="flex flex-1 flex-col p-5">
                                @if ($post->category)
                                    <a href="{{ route('blog.category', $post->category->slug) }}"
                                       class="inline-flex self-start items-center rounded-full bg-brand-50 text-brand text-xs font-bold px-3 py-1 mb-3 hover:bg-brand hover:text-white transition-colors">
                                        {{ $post->category->name_ar }}
                                    </a>
                                @endif
                                <h2 class="text-lg font-bold leading-snug mb-2 text-pretty">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="group-hover:text-brand transition-colors line-clamp-2">{{ $post->title_ar }}</a>
                                </h2>
                                @if ($post->excerpt)
                                    <p class="text-muted text-sm leading-relaxed flex-1 line-clamp-3">{{ $post->excerpt }}</p>
                                @endif
                                <div class="mt-4 flex items-center gap-3 text-xs text-muted">
                                    @if ($post->published_at)
                                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale('ar')->isoFormat('D MMMM YYYY') }}</time>
                                        <span aria-hidden="true">·</span>
                                    @endif
                                    <span>{{ $post->content()->readingMinutes }} دقايق قراءة</span>
                                </div>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>

            {{-- ترقيم الصفحات --}}
            @if ($posts->hasPages())
                <div class="mt-12">
                    {{ $posts->onEachSide(1)->links('pagination.blog') }}
                </div>
            @endif
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-16 px-4">
                <p class="text-muted text-lg">لسه مفيش مقالات في القسم ده — بنجهّز المحتوى.</p>
                <a href="{{ url('/') }}#booking"
                   class="inline-block mt-5 px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                    احجز فني دلوقتي
                </a>
            </div>
        @endif
    </section>
@endsection
