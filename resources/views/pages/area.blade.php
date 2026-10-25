@extends('layouts.app')

{{--
    صفحة منطقة مفردة (/areas/{slug}) — المرحلة 3.
    متغيرات: $area, $title, $subtitle, $canonical, $services, $serviceIcons,
             $brands, $breadcrumbs
--}}

@push('schema')
    {{-- Service schema بـ areaServed = اسم المنطقة (من غير عنوان فرع وهمي). --}}
    <x-schema.service
        :name="'صيانة وإصلاح تكييفات في ' . $area->name_ar"
        :description="$area->seo_description ?: $area->excerpt"
        :url="$canonical"
        :areaServed="$area->name_ar" />

    @if (! empty($area->faqs))
        <x-schema.faq-page :items="$area->faqs" />
    @endif
@endpush

@section('content')

    {{-- ============ الهيرو ============ --}}
    <x-breadcrumbs :items="$breadcrumbs" />

    <header class="mx-auto max-w-6xl px-4 pt-6 pb-10 md:pb-12">
        <div class="flex items-start gap-4 md:gap-5">
            <div class="shrink-0 w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-brand-50 text-brand flex items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="w-8 h-8 md:w-9 md:h-9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-6-5.3-6-10a6 6 0 0 1 12 0c0 4.7-6 10-6 10Z"/><circle cx="12" cy="11" r="2.2"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-muted font-semibold mb-1">فني تكييف في {{ $title }}</p>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-3">صيانة تكييف {{ $title }}</h1>
                <p class="text-muted text-lg leading-relaxed max-w-2xl">
                    {{ $subtitle ?: ('فني تكييف قريب منك في ' . $title . ' — صيانة وغسيل وشحن وتركيب وإصلاح أعطال، والدفع بعد الشغل.') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-7">
            <a href="#booking"
               class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                احجز فني في {{ $title }}
            </a>
            @if (\App\Support\Site::hasPhone())
            <a href="tel:{{ \App\Support\Site::phoneTel() ?? \App\Support\Site::phoneDisplay() }}"
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

    {{-- ============ المقدمة (طبيعة المنطقة والمباني) ============ --}}
    @if (filled($area->intro))
        <section class="mx-auto max-w-6xl px-4 pb-4">
            <div class="max-w-3xl space-y-4 text-lg leading-loose text-ink/90">
                @foreach (preg_split('/\n\s*\n/', trim($area->intro)) as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ مشاكل التكييف الشائعة في المنطقة ============ --}}
    @if (! empty($area->common_problems))
        <section class="bg-surface scroll-mt-20">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <div class="max-w-2xl mb-6">
                    <h2 class="text-2xl md:text-3xl font-extrabold mb-2">مشاكل التكييف الأكثر شيوعاً في {{ $title }}</h2>
                    <p class="text-muted leading-relaxed">حاجات بنشوفها كتير في المنطقة — لو واجهت واحدة منها كلّمنا.</p>
                </div>
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach ($area->common_problems as $problem)
                        <li class="flex items-start gap-3 rounded-xl bg-white border border-line px-5 py-4">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-brand-50 text-brand flex items-center justify-center mt-0.5" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                            </span>
                            <span class="leading-relaxed">{{ $problem }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ============ الخدمات الأكثر طلباً في المنطقة ============ --}}
    @if ($services->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-6">الخدمات الأكثر طلباً في {{ $title }}</h2>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $svc)
                    @php
                        // لو فيه صفحة (منطقة × خدمة) منشورة، نوصّل ليها بدل صفحة الخدمة العامة.
                        $hasAreaPage = in_array($svc->slug, $areaServiceSlugs ?? [], true);
                        $svcUrl = $hasAreaPage ? '/areas/' . $area->slug . '/' . $svc->slug : '/services/' . $svc->slug;
                    @endphp
                    <a href="{{ $svcUrl }}"
                       class="group flex flex-col rounded-2xl border border-line bg-white p-6 hover:border-brand hover:shadow-lg hover:shadow-brand/5 transition-all">
                        <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4 group-hover:bg-brand group-hover:text-white transition-colors">
                            <x-service-icon :name="$serviceIcons[$svc->slug] ?? 'maintenance'" />
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ $hasAreaPage ? $svc->name_ar . ' في ' . $title : $svc->name_ar }}</h3>
                        @if ($svc->excerpt)
                            <p class="text-muted text-sm leading-relaxed flex-1">{{ $svc->excerpt }}</p>
                        @endif
                        <span class="inline-flex items-center gap-1.5 text-brand font-bold text-sm mt-4 group-hover:gap-2.5 transition-all">
                            التفاصيل
                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ أشهر الأحياء + زمن الوصول ============ --}}
    <section class="mx-auto max-w-6xl px-4 pb-4">
        <div class="grid gap-8 lg:grid-cols-2">
            @if (! empty($area->neighborhoods))
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold mb-3">أحياء وكمبوندات بنغطيها في {{ $title }}</h2>
                    <ul class="flex flex-wrap gap-2.5">
                        @foreach ($area->neighborhoods as $hood)
                            @php $hoodName = is_array($hood) ? ($hood['name'] ?? '') : $hood; $hoodType = is_array($hood) ? ($hood['type'] ?? null) : null; @endphp
                            @if (filled($hoodName))
                                <li class="bg-surface border border-line rounded-full px-4 py-2 text-sm font-medium">
                                    {{ $hoodName }}@if ($hoodType === 'كمبوند')<span class="text-xs text-muted"> · كمبوند</span>@endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                    <p class="text-muted text-sm leading-relaxed mt-4">مش لاقي حيّك في القايمة؟ كلّمنا ونشوف أقرب معاد ممكن نوصلك فيه.</p>
                </div>
            @endif

            @if (filled($area->eta_note))
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold mb-3">زمن الوصول التقريبي</h2>
                    <div class="rounded-2xl border border-dashed border-line bg-surface p-6">
                        <p class="text-ink leading-relaxed">{{ $area->eta_note }}</p>
                        <p class="text-xs text-muted mt-2">بيختلف حسب زحمة الطريق وتوفّر الفنيين — بنأكدلك المعاد وقت الحجز.</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ============ الماركات اللي بنصلّحها ============ --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
        <div class="max-w-2xl mb-6">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-2">بنصلّح كل الماركات في {{ $title }}</h2>
            <p class="text-muted leading-relaxed">إحنا مش توكيل لماركة بذاتها — فنيينا متدربين على أنواع وموديلات مختلفة. قولنا نوع جهازك وموديله وإحنا نظبّطك.</p>
        </div>
        @if ($brands->isNotEmpty())
            <ul class="flex flex-wrap gap-2.5 mb-5">
                @foreach ($brands as $brand)
                    <li>
                        <a href="{{ '/brands/' . $brand->slug }}"
                           class="inline-block bg-white border border-line rounded-full px-4 py-2 text-sm font-medium hover:border-brand hover:text-brand transition-colors">
                            {{ $brand->name_ar }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        <a href="{{ route('brands.index') }}"
           class="inline-flex items-center gap-1.5 text-brand font-bold text-sm hover:gap-2.5 transition-all">
            شوف كل الماركات اللي بنتعامل معاها
            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </a>
    </section>

    {{-- ============ الأسئلة الشائعة ============ --}}
    @if (! empty($area->faqs))
        <section class="bg-surface scroll-mt-20">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-6">أسئلة من عملاء {{ $title }}</h2>
                <div class="max-w-3xl space-y-3">
                    @foreach ($area->faqs as $faq)
                        <details class="group rounded-xl border border-line bg-white p-5">
                            <summary class="flex items-center justify-between cursor-pointer font-bold text-lg">
                                {{ $faq['q'] ?? '' }}
                                <svg class="faq-icon w-5 h-5 text-brand transition-transform shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            </summary>
                            <p class="text-muted leading-relaxed mt-3">{{ $faq['a'] ?? '' }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ فورم الحجز (بيحدد المنطقة تلقائياً) ============ --}}
    <section id="booking" class="bg-navy scroll-mt-20">
        <div class="mx-auto max-w-4xl px-4 py-16 md:py-20">
            <x-booking-form
                :heading="'احجز فني في ' . $title"
                sub="قولنا بياناتك والخدمة والوقت المناسب، وهنتواصل معاك نأكد المعاد والسعر التقريبي."
                :area="$title" />
        </div>
    </section>

@endsection
