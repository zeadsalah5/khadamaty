@extends('layouts.app')

{{-- معرض الأعمال — بيعرض الأعمال المنشورة. متغيرات: $projects, $items, $breadcrumbs --}}

@if (! empty($items))
    @push('schema')
        <x-schema.item-list :items="$items" name="معرض أعمال خدماتي" />
    @endpush
@endif

@section('content')
    @php $years = (int) site('experience_years', 15); @endphp

    <x-page-hero
        title="معرض أعمالنا"
        :subtitle="'نماذج حقيقية من شغلنا في تركيب وصيانة وإصلاح التكييفات — خبرة أكتر من ' . $years . ' سنة.'"
        :breadcrumbs="$breadcrumbs" />

    {{-- شريط الخبرة --}}
    <section class="mx-auto max-w-6xl px-4 -mt-2 mb-2">
        <div class="flex flex-wrap items-center gap-4 rounded-2xl bg-surface border border-line p-5 md:p-6">
            <div class="flex items-center gap-3">
                <span class="shrink-0 w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center font-extrabold text-xl">{{ $years }}+</span>
                <div>
                    <div class="font-extrabold text-lg">خبرة أكتر من {{ $years }} سنة</div>
                    <p class="text-muted text-sm">في تركيب وصيانة وإصلاح التكييفات بكل أنواعها.</p>
                </div>
            </div>
            <a href="#booking-cta" class="ms-auto inline-flex items-center px-5 py-2.5 rounded-full bg-brand hover:bg-brand-600 text-white font-bold text-sm transition-colors">
                اطلب نفس الشغل
            </a>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-8 md:py-10">
        @if ($projects->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <article class="group flex flex-col overflow-hidden rounded-2xl border border-line bg-white hover:border-brand hover:shadow-xl hover:shadow-brand/5 transition-all">
                        <a href="{{ route('portfolio.show', $project->slug) }}" class="block aspect-[16/11] overflow-hidden bg-surface relative">
                            @if ($project->coverUrl())
                                <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}"
                                     width="640" height="440" loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-navy via-[#1E3A5F] to-[#16343a] flex items-center justify-center text-white/80" aria-hidden="true">
                                    <x-service-icon name="install" class="w-12 h-12" />
                                </div>
                            @endif
                            @if ($project->is_featured)
                                <span class="absolute top-3 start-3 rounded-full bg-brand text-white text-xs font-bold px-3 py-1">مميّز</span>
                            @endif
                        </a>
                        <div class="flex flex-col flex-1 p-5">
                            @if ($project->service_type || $project->area_name)
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @if ($project->service_type)
                                        <span class="rounded-full bg-brand-50 text-brand text-xs font-bold px-2.5 py-1">{{ $project->service_type }}</span>
                                    @endif
                                    @if ($project->area_name)
                                        <span class="rounded-full bg-surface border border-line text-muted text-xs font-medium px-2.5 py-1">{{ $project->area_name }}</span>
                                    @endif
                                </div>
                            @endif
                            <h2 class="text-lg font-bold mb-1.5 group-hover:text-brand transition-colors">{{ $project->title }}</h2>
                            @if ($project->summary)
                                <p class="text-muted text-sm leading-relaxed flex-1">{{ $project->summary }}</p>
                            @endif
                            <span class="inline-flex items-center gap-1.5 mt-4 text-brand font-bold text-sm">
                                شوف التفاصيل
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-16 px-4">
                <p class="text-muted text-lg">بنجهّز معرض أعمالنا — قريباً هتشوف نماذج من شغلنا.</p>
                <a href="{{ route('request-service') }}"
                   class="inline-block mt-5 px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                    احجز فني دلوقتي
                </a>
            </div>
        @endif
    </section>

    {{-- CTA --}}
    <section id="booking-cta" class="bg-navy scroll-mt-20">
        <div class="mx-auto max-w-4xl px-4 py-14 md:py-16 text-center">
            <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-3">عايز شغل بنفس المستوى؟</h2>
            <p class="text-white/70 leading-relaxed mb-6 max-w-2xl mx-auto">فريقنا بخبرة أكتر من {{ $years }} سنة جاهز لخدمتك. احجز معاينة أو كلّمنا دلوقتي.</p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('request-service') }}" class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">احجز فني</a>
                @if (\App\Support\Site::hasWhatsapp())
                    <a href="https://wa.me/{{ \App\Support\Site::whatsappNumber() }}" target="_blank" rel="noopener" class="inline-flex items-center px-6 py-3 rounded-full bg-accent hover:bg-accent-600 text-white font-bold transition-colors">واتساب</a>
                @endif
            </div>
        </div>
    </section>
@endsection
