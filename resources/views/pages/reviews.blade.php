@extends('layouts.app')

{{--
    صفحة آراء العملاء.
    - بتعرض التقييمات المعتمدة بس (approved) من جدول reviews.
    - فاضي => "لسه مفيش تقييمات" + الصفحة noindex (من الـ Controller).
    - schema التقييمات بيطلع بس لو فيه تقييمات حقيقية معتمدة.
    متغيرات: $reviews, $breadcrumbs
--}}

@if ($reviews->isNotEmpty())
    @push('schema')
        <x-schema.review-list :reviews="$reviews" :url="app('App\\Support\\Seo')->getCanonical()" />
    @endpush
@endif

@section('content')
    <x-page-hero
        title="آراء العملاء"
        subtitle="تقييمات حقيقية من ناس جرّبت خدماتنا. كل تقييم بيتراجع قبل ما يتنشر."
        :breadcrumbs="$breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
        @if ($reviews->isNotEmpty())
            @php
                $avg = round($reviews->avg('rating'), 1);
            @endphp

            {{-- ملخّص التقييم --}}
            <div class="flex flex-wrap items-center gap-4 mb-8">
                <div class="flex items-center gap-1 text-brand" aria-hidden="true">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="{{ $i <= round($avg) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 17.8 6.8 19.2l1-5.8L3.5 9.2l5.9-.9L12 3Z"/></svg>
                    @endfor
                </div>
                <p class="font-bold text-lg">{{ $avg }} من 5
                    <span class="text-muted font-normal">— {{ $reviews->count() }} تقييم</span>
                </p>
            </div>

            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($reviews as $review)
                    <li class="rounded-2xl border border-line bg-white p-6">
                        <div class="flex items-center gap-1 text-brand mb-3" role="img"
                             aria-label="{{ $review->rating }} من 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="{{ $i <= $review->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 17.8 6.8 19.2l1-5.8L3.5 9.2l5.9-.9L12 3Z"/></svg>
                            @endfor
                        </div>
                        <p class="text-ink/90 leading-relaxed mb-4 break-words">{{ $review->body }}</p>
                        <div class="text-sm">
                            <span class="font-bold break-words">{{ $review->name }}</span>
                            @if ($review->area || $review->service)
                                <span class="text-muted">
                                    — {{ collect([$review->area, $review->service])->filter()->implode(' · ') }}
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            {{-- حالة فاضية — من غير أي تقييمات مفبركة --}}
            <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-16 px-4 max-w-2xl mx-auto">
                <div class="w-14 h-14 rounded-2xl bg-white border border-line flex items-center justify-center mx-auto mb-5 text-brand">
                    <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 17.8 6.8 19.2l1-5.8L3.5 9.2l5.9-.9L12 3Z"/></svg>
                </div>
                <h2 class="text-xl md:text-2xl font-extrabold mb-2">لسه مفيش تقييمات</h2>
                <p class="text-muted leading-relaxed mb-6">
                    هنعرض هنا آراء عملائنا الحقيقية أول ما تتجمّع وتتراجع. جرّب خدمتنا وكن أول من يقيّمنا.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('request-service') }}"
                       class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                        احجز فني دلوقتي
                    </a>
                    <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-bold transition-colors">
                        تواصل معانا
                    </a>
                </div>
            </div>
        @endif
    </section>
@endsection
