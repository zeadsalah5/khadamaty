@extends('layouts.app')

{{--
    صفحة (منطقة × خدمة) — K2 المناطق (SEO عضوي).
    متغيرات: $page, $area, $service, $title, $canonical, $serviceIcon,
             $bookingService, $areaPath, $servicePath, $breadcrumbs
    بتستهدف «صيانة تكييف {الخدمة} في {المنطقة}» + «فني {الخدمة} {المنطقة}».
--}}

@push('schema')
    <x-schema.service
        :name="$page->h1"
        :description="$page->seo_description ?: $service->excerpt"
        :url="$canonical"
        :areaServed="$area->name_ar" />

    @if (! empty($page->faqs))
        <x-schema.faq-page :items="$page->faqs" />
    @endif
@endpush

@section('content')

    <x-breadcrumbs :items="$breadcrumbs" />

    {{-- ============ الهيرو ============ --}}
    <header class="mx-auto max-w-6xl px-4 pt-6 pb-10 md:pb-12">
        <div class="flex items-start gap-4 md:gap-5">
            <div class="shrink-0 w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-brand-50 text-brand flex items-center justify-center">
                <x-service-icon :name="$serviceIcon" class="w-8 h-8 md:w-9 md:h-9" />
            </div>
            <div class="min-w-0">
                <p class="text-muted font-semibold mb-1">{{ $service->name_ar }} — {{ $area->name_ar }}</p>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-3">{{ $title }}</h1>
                <p class="text-muted text-lg leading-relaxed max-w-2xl">
                    فني {{ $service->name_ar }} في {{ $area->name_ar }} — بنوصلك، معاينة قبل الشغل، والدفع بعد ما تتأكد إن كل حاجة تمام.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-7">
            <a href="#booking"
               class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                احجز فني في {{ $area->name_ar }}
            </a>
            @if (\App\Support\Site::hasPhone())
                <a href="tel:{{ \App\Support\Site::phoneTel() }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-bold transition-colors">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.3.2 2.5.57 3.6a1 1 0 0 1-.25 1l-2.2 2.2Z"/></svg>
                    اتصل: {{ \App\Support\Site::phoneDisplay() }}
                </a>
            @endif
            @if (\App\Support\Site::hasWhatsapp())
                <a href="https://wa.me/{{ \App\Support\Site::whatsappNumber() }}" target="_blank" rel="noopener"
                   class="inline-flex items-center px-6 py-3 rounded-full border border-line hover:border-accent hover:text-accent font-bold transition-colors">
                    اسأل على واتساب
                </a>
            @endif
        </div>
    </header>

    {{-- ============ المقدمة ============ --}}
    @if (filled($page->intro))
        <section class="mx-auto max-w-6xl px-4 pb-4">
            <div class="max-w-3xl space-y-4 text-lg leading-loose text-ink/90">
                @foreach (preg_split('/\n\s*\n/', trim($page->intro)) as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ إزاي بنشتغل ============ --}}
    @if (! empty($page->steps))
        <section class="bg-surface scroll-mt-20">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-8">إزاي بنشتغل في {{ $area->name_ar }}</h2>
                <ol class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($page->steps as $i => $step)
                        <li class="relative rounded-2xl bg-white border border-line p-6">
                            <span class="absolute -top-4 start-6 w-9 h-9 rounded-full bg-brand text-white font-bold flex items-center justify-center text-lg">
                                {{ $i + 1 }}
                            </span>
                            <h3 class="text-lg font-bold mt-3 mb-2">{{ $step['title'] ?? '' }}</h3>
                            <p class="text-muted text-sm leading-relaxed">{{ $step['desc'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- ============ المدة والسعر (يملأها صاحب الموقع) ============ --}}
    @if (filled($page->price_note) || filled($page->duration_note))
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <div class="grid gap-4 sm:grid-cols-2 max-w-3xl">
                @if (filled($page->price_note))
                    <div class="rounded-2xl border border-dashed border-line bg-surface p-6">
                        <div class="text-sm font-bold text-muted mb-1">السعر</div>
                        <p class="text-ink leading-relaxed">{{ $page->price_note }}</p>
                        <p class="text-xs text-muted mt-2">السعر النهائي بيتأكد وقت المعاينة قبل أي شغل.</p>
                    </div>
                @endif
                @if (filled($page->duration_note))
                    <div class="rounded-2xl border border-dashed border-line bg-surface p-6">
                        <div class="text-sm font-bold text-muted mb-1">المدة التقريبية</div>
                        <p class="text-ink leading-relaxed">{{ $page->duration_note }}</p>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ============ الأسئلة الشائعة ============ --}}
    @if (! empty($page->faqs))
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-6">أسئلة شائعة عن {{ $title }}</h2>
            <div class="max-w-3xl space-y-3">
                @foreach ($page->faqs as $faq)
                    <details class="group rounded-xl border border-line bg-white p-5">
                        <summary class="flex items-center justify-between cursor-pointer font-bold text-lg">
                            {{ $faq['q'] ?? '' }}
                            <svg class="faq-icon w-5 h-5 text-brand transition-transform shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </summary>
                        <p class="text-muted leading-relaxed mt-3">{{ $faq['a'] ?? '' }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ روابط داخلية ============ --}}
    <section class="bg-surface scroll-mt-20">
        <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 class="text-xl md:text-2xl font-extrabold mb-4">تفاصيل أكتر</h2>
            <ul class="space-y-2">
                <li>
                    <a href="{{ $areaPath }}" class="inline-flex items-center gap-1.5 text-brand font-bold text-sm hover:gap-2.5 transition-all">
                        كل خدمات التكييف في {{ $area->name_ar }}
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                </li>
                <li>
                    <a href="{{ $servicePath }}" class="inline-flex items-center gap-1.5 text-brand font-bold text-sm hover:gap-2.5 transition-all">
                        {{ $service->name_ar }} في كل المناطق
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                </li>
            </ul>
        </div>
    </section>

    {{-- ============ فورم الحجز (بيحدد المنطقة والخدمة) ============ --}}
    <section id="booking" class="bg-navy scroll-mt-20">
        <div class="mx-auto max-w-4xl px-4 py-16 md:py-20">
            <x-booking-form
                :heading="'احجز ' . $service->name_ar . ' في ' . $area->name_ar"
                sub="قولنا بياناتك والوقت المناسب، وهنتواصل معاك نأكد المعاد والسعر التقريبي."
                :service="$bookingService"
                :area="$area->name_ar"
                :show-appliance="false" />
        </div>
    </section>

@endsection
