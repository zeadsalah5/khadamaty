@extends('layouts.app')

{{--
    صفحة مقال مفرد (/blog/{slug}) — المرحلة 5.
    متغيرات: $post, $title, $subtitle, $canonical, $content (ArticleContent),
             $related, $services, $serviceIcons, $breadcrumbs
    المتن متخزّن Markdown، بيترندر HTML آمن + فهرس محتويات تلقائي من H2.
--}}

@php
    $published = $post->published_at;
    $modified = $post->updated_at;
    $showModified = $published && $modified && $modified->gt($published->copy()->addDay());
@endphp

@push('schema')
    <x-schema.article
        :headline="$post->title_ar"
        :description="$post->seo_description ?: $post->excerpt"
        :url="$canonical"
        :image="$post->cover ? asset('images/blog/' . $post->cover) : null"
        :datePublished="$published?->toAtomString()"
        :dateModified="$modified?->toAtomString()" />
@endpush

@section('content')
    <x-breadcrumbs :items="$breadcrumbs" />

    {{-- ============ هيدر المقال ============ --}}
    <header class="mx-auto max-w-3xl px-4 pt-6 pb-8">
        @if ($post->category)
            <a href="{{ route('blog.category', $post->category->slug) }}"
               class="inline-flex items-center rounded-full bg-brand-50 text-brand text-sm font-bold px-4 py-1.5 mb-4 hover:bg-brand hover:text-white transition-colors">
                {{ $post->category->name_ar }}
            </a>
        @endif

        <h1 class="text-3xl md:text-4xl font-extrabold leading-tight mb-4 text-balance">{{ $title }}</h1>

        @if ($subtitle)
            <p class="text-muted text-lg leading-relaxed mb-5">{{ $subtitle }}</p>
        @endif

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted border-t border-line pt-5">
            @if ($published)
                <span class="inline-flex items-center gap-1.5">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    نُشر <time datetime="{{ $published->toDateString() }}">{{ $published->locale('ar')->isoFormat('D MMMM YYYY') }}</time>
                </span>
            @endif
            @if ($showModified)
                <span class="inline-flex items-center gap-1.5">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
                    آخر تحديث <time datetime="{{ $modified->toDateString() }}">{{ $modified->locale('ar')->isoFormat('D MMMM YYYY') }}</time>
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                {{ $content->readingMinutes }} دقايق قراءة
            </span>
        </div>
    </header>

    {{-- ============ صورة الغلاف ============ --}}
    @if (filled($post->cover))
        <figure class="mx-auto max-w-3xl px-4 mb-8">
            <x-img :src="'images/blog/' . $post->cover" :alt="$post->title_ar"
                   :width="1200" :height="675" :lazy="false"
                   class="w-full rounded-2xl border border-line object-cover aspect-video" />
        </figure>
    @endif

    {{-- ============ الجسم: فهرس + متن ============ --}}
    <div class="mx-auto max-w-6xl px-4 pb-4 lg:grid lg:grid-cols-[18rem_1fr] lg:gap-10">

        {{-- فهرس المحتويات --}}
        @if (! empty($content->toc))
            <aside class="lg:order-last mb-8 lg:mb-0">
                <nav aria-label="فهرس المحتويات"
                     class="lg:sticky lg:top-24 rounded-2xl border border-line bg-surface p-5">
                    <h2 class="text-sm font-extrabold text-ink mb-3 flex items-center gap-2">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        محتويات المقال
                    </h2>
                    <ol class="space-y-2 text-sm">
                        @foreach ($content->toc as $item)
                            <li>
                                <a href="#{{ $item['id'] }}"
                                   class="text-muted hover:text-brand transition-colors leading-snug block">{{ $item['text'] }}</a>
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </aside>
        @endif

        {{-- متن المقال --}}
        <article class="min-w-0 max-w-3xl">
            <div class="article-body">
                {!! $content->html !!}
            </div>

            {{-- خدمات مناسبة (روابط داخلية) --}}
            @if ($services->isNotEmpty())
                <div class="mt-10 rounded-2xl border border-line bg-surface p-6">
                    <h2 class="text-lg font-extrabold mb-4">خدمات ممكن تهمّك</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($services as $svc)
                            <a href="{{ '/services/' . $svc->slug }}"
                               class="group flex items-center gap-3 rounded-xl border border-line bg-white px-4 py-3 hover:border-brand transition-colors">
                                <span class="shrink-0 w-10 h-10 rounded-lg bg-brand-50 text-brand flex items-center justify-center group-hover:bg-brand group-hover:text-white transition-colors">
                                    <x-service-icon :name="$serviceIcons[$svc->slug] ?? 'maintenance'" />
                                </span>
                                <span class="font-bold leading-snug group-hover:text-brand transition-colors">{{ $svc->name_ar }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- CTA حجز --}}
            <div class="mt-10 rounded-2xl bg-navy text-white p-7 md:p-9 text-center">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-2">محتاج فني تكييفات؟</h2>
                <p class="text-white/80 leading-relaxed mb-6 max-w-xl mx-auto">احجز معاينة وصيانة بأيدي فنيين متخصصين، بأسعار واضحة، والدفع بعد ما الشغل يخلص.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ url('/') }}#booking"
                       class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                        احجز فني دلوقتي
                    </a>
                    <a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center px-6 py-3 rounded-full bg-accent hover:bg-accent-600 text-white font-bold transition-colors">
                        اسأل على واتساب
                    </a>
                </div>
            </div>
        </article>
    </div>

    {{-- ============ مقالات ذات صلة ============ --}}
    @if ($related->isNotEmpty())
        <section class="bg-surface mt-12">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-6">مقالات ذات صلة</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $rel)
                        <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-white hover:border-brand transition-colors">
                            <a href="{{ route('blog.show', $rel->slug) }}" class="block aspect-video bg-surface overflow-hidden">
                                @if (filled($rel->cover))
                                    <x-img :src="'images/blog/' . $rel->cover" :alt="$rel->title_ar" :width="480" :height="270"
                                           class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-muted" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                                    </div>
                                @endif
                            </a>
                            <div class="p-5">
                                <h3 class="font-bold leading-snug text-pretty">
                                    <a href="{{ route('blog.show', $rel->slug) }}" class="group-hover:text-brand transition-colors line-clamp-2">{{ $rel->title_ar }}</a>
                                </h3>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
