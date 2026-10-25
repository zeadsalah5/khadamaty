@extends('layouts.app')

{{--
    صفحة خدمة مفردة (/services/{slug}) — المرحلة 2.
    متغيرات: $service, $title, $subtitle, $canonical, $icon, $bookingService,
             $related, $relatedIcons, $areas, $breadcrumbs
--}}

@push('schema')
    <x-schema.service
        :name="$service->name_ar"
        :description="$service->seo_description ?: $service->excerpt"
        :url="$canonical"
        :areaServed="$areas ?: null" />

    @if (! empty($service->faqs))
        <x-schema.faq-page :items="$service->faqs" />
    @endif
@endpush

@section('content')

    {{-- ============ الهيرو ============ --}}
    <x-breadcrumbs :items="$breadcrumbs" />

    <header class="mx-auto max-w-6xl px-4 pt-6 pb-10 md:pb-12">
        <div class="flex items-start gap-4 md:gap-5">
            <div class="shrink-0 w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-brand-50 text-brand flex items-center justify-center">
                <x-service-icon :name="$icon" class="w-8 h-8 md:w-9 md:h-9" />
            </div>
            <div class="min-w-0">
                <h1 class="text-3xl md:text-4xl font-extrabold mb-3">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="text-muted text-lg leading-relaxed max-w-2xl">{{ $subtitle }}</p>
                @endif
                @php $hasWarranty = filled(site('warranty_scope')) && filled(site('warranty_exclusions')); @endphp
                @if ($hasWarranty)
                    <span class="inline-flex items-center gap-1.5 mt-3 rounded-full bg-accent/10 text-accent-600 border border-accent/30 px-3 py-1 text-sm font-bold">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                        ضمان {{ (int) site('warranty_months', 6) }} شهور على الخدمة
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-7">
            <a href="#booking"
               class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                احجز الخدمة
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

    {{-- ============ المقدمة ============ --}}
    @if (filled($service->body))
        <section class="mx-auto max-w-6xl px-4 pb-4">
            <div class="max-w-3xl space-y-4 text-lg leading-loose text-ink/90">
                @foreach (preg_split('/\n\s*\n/', trim($service->body)) as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ الخدمة بتشمل إيه ============ --}}
    @if (! empty($service->includes))
        <section class="mx-auto max-w-6xl px-4 py-10 md:py-12">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-6">الخدمة بتشمل إيه</h2>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($service->includes as $item)
                    <li class="flex items-start gap-3 rounded-xl border border-line bg-white px-5 py-4">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-accent mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span class="leading-relaxed">{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ============ الخدمة ميشملش إيه (تتخفى لو فاضية) ============ --}}
    @if (! empty($service->excludes))
        <section class="mx-auto max-w-6xl px-4 pb-4 md:pb-6">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-6">الخدمة ميشملش إيه</h2>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($service->excludes as $item)
                    <li class="flex items-start gap-3 rounded-xl border border-line bg-surface px-5 py-4">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-brand mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        <span class="leading-relaxed">{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ============ الأعراض ============ --}}
    @if (! empty($service->symptoms))
        <section class="bg-surface scroll-mt-20">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <div class="max-w-2xl mb-6">
                    <h2 class="text-2xl md:text-3xl font-extrabold mb-2">الأعراض اللي بتقولك إنك محتاج الخدمة دي</h2>
                    <p class="text-muted leading-relaxed">لو لاقيت واحدة أو أكتر من دي، يبقى الوقت مناسب تكلّمنا.</p>
                </div>
                <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($service->symptoms as $symptom)
                        <li class="flex items-start gap-3 rounded-xl bg-white border border-line px-5 py-4">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-brand-50 text-brand flex items-center justify-center mt-0.5" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                            </span>
                            <span class="leading-relaxed text-sm">{{ $symptom }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ============ إزاي بنشتغل ============ --}}
    @if (! empty($service->steps))
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-8">إزاي بنشتغل</h2>
            <ol class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($service->steps as $i => $step)
                    <li class="relative rounded-2xl bg-white border border-line p-6">
                        <span class="absolute -top-4 start-6 w-9 h-9 rounded-full bg-brand text-white font-bold flex items-center justify-center text-lg">
                            {{ $i + 1 }}
                        </span>
                        <h3 class="text-lg font-bold mt-3 mb-2">{{ $step['title'] ?? '' }}</h3>
                        <p class="text-muted text-sm leading-relaxed">{{ $step['desc'] ?? '' }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    {{-- ============ إيه اللي بيأثر على التكلفة (تتخفى لو فاضية) ============ --}}
    @if (! empty($service->cost_factors))
        <section class="mx-auto max-w-6xl px-4 py-10 md:py-12">
            <div class="max-w-2xl mb-6">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-2">إيه اللي بيأثر على التكلفة</h2>
                <p class="text-muted leading-relaxed">التكلفة بتختلف حسب العوامل دي — بنوضّحها لك قبل أي شغل.</p>
            </div>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($service->cost_factors as $factor)
                    <li class="flex items-start gap-3 rounded-xl border border-line bg-white px-5 py-4">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-brand mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span class="leading-relaxed">{{ $factor }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ============ المدة والسعر (placeholders) ============ --}}
    @if (filled($service->price_note) || filled($service->duration_note))
        <section class="mx-auto max-w-6xl px-4 pb-4">
            <div class="grid gap-4 sm:grid-cols-2 max-w-3xl">
                @if (filled($service->price_note))
                    <div class="rounded-2xl border border-dashed border-line bg-surface p-6">
                        <div class="text-sm font-bold text-muted mb-1">السعر</div>
                        <p class="text-ink leading-relaxed">{{ $service->price_note }}</p>
                        <p class="text-xs text-muted mt-2">السعر النهائي بيتأكد وقت المعاينة قبل أي شغل.</p>
                    </div>
                @endif
                @if (filled($service->duration_note))
                    <div class="rounded-2xl border border-dashed border-line bg-surface p-6">
                        <div class="text-sm font-bold text-muted mb-1">المدة التقريبية</div>
                        <p class="text-ink leading-relaxed">{{ $service->duration_note }}</p>
                        <p class="text-xs text-muted mt-2">بتختلف حسب حالة الجهاز وعددها.</p>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ============ الضمان (يظهر بس لو النطاق والاستثناءات متملّيين) ============ --}}
    @if (filled(site('warranty_scope')) && filled(site('warranty_exclusions')))
        <section class="mx-auto max-w-6xl px-4 py-10 md:py-12">
            <div class="max-w-3xl rounded-2xl border border-accent/30 bg-accent/5 p-6 md:p-7">
                <div class="flex items-center gap-2 mb-3">
                    <svg viewBox="0 0 24 24" class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    <h2 class="text-2xl font-extrabold">ضمان {{ (int) site('warranty_months', 6) }} شهور</h2>
                </div>
                <p class="text-ink leading-relaxed mb-2"><span class="font-bold">نطاق الضمان:</span> {{ site('warranty_scope') }}</p>
                <p class="text-muted leading-relaxed"><span class="font-bold text-ink">الاستثناءات:</span> {{ site('warranty_exclusions') }}</p>
            </div>
        </section>
    @endif

    {{-- ============ الأسئلة الشائعة ============ --}}
    @if (! empty($service->faqs))
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-6">أسئلة شائعة عن {{ $title }}</h2>
            <div class="max-w-3xl space-y-3">
                @foreach ($service->faqs as $faq)
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

    {{-- ============ خدمات مرتبطة ============ --}}
    @if ($related->isNotEmpty())
        <section class="bg-surface scroll-mt-20">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-6">خدمات مرتبطة</h2>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $rel)
                        <a href="{{ '/services/' . $rel->slug }}"
                           class="group flex flex-col rounded-2xl border border-line bg-white p-6 hover:border-brand hover:shadow-lg hover:shadow-brand/5 transition-all">
                            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4 group-hover:bg-brand group-hover:text-white transition-colors">
                                <x-service-icon :name="$rel->icon ?: ($relatedIcons[$rel->slug] ?? 'maintenance')" />
                            </div>
                            <h3 class="text-lg font-bold mb-2">{{ $rel->name_ar }}</h3>
                            @if ($rel->excerpt)
                                <p class="text-muted text-sm leading-relaxed flex-1">{{ $rel->excerpt }}</p>
                            @endif
                            <span class="inline-flex items-center gap-1.5 text-brand font-bold text-sm mt-4 group-hover:gap-2.5 transition-all">
                                التفاصيل
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ الخدمة حسب الماركة (صفحات ماركة × خدمة) ============ --}}
    @if (! empty($brandPages) && $brandPages->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <div class="max-w-2xl mb-6">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-2">{{ $title }} حسب الماركة</h2>
                <p class="text-muted leading-relaxed">صفحات بتفاصيل أكتر عن الخدمة دي لكل ماركة — أعطالها الشائعة وطريقة شغلنا معاها.</p>
            </div>
            <ul class="flex flex-wrap gap-2.5">
                @foreach ($brandPages as $bp)
                    <li>
                        <a href="{{ $bp['url'] }}"
                           class="inline-block bg-surface border border-line rounded-full px-4 py-2 text-sm font-medium hover:border-brand hover:text-brand transition-colors">
                            {{ $title }} {{ $bp['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ============ مناطق الخدمة + الماركات ============ --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
        <div class="grid gap-8 lg:grid-cols-2">
            @if (! empty($areas))
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold mb-2">بنغطي منطقتك؟</h2>
                    <p class="text-muted leading-relaxed mb-4">بنخدم {{ site('areas_label') }} والمناطق المحيطة. مش لاقي منطقتك؟ كلّمنا ونشوفها.</p>
                    @php $areaLinks = \App\Models\Area::query()->published()->pluck('slug', 'name_ar'); @endphp
                    <ul class="flex flex-wrap gap-2.5">
                        @foreach ($areas as $area)
                            <li>
                                @if ($areaLinks->has($area))
                                    <a href="{{ '/areas/' . $areaLinks->get($area) }}"
                                       class="inline-block bg-surface border border-line rounded-full px-4 py-2 text-sm font-medium hover:border-brand hover:text-brand transition-colors">{{ $area }}</a>
                                @else
                                    <span class="inline-block bg-surface border border-line rounded-full px-4 py-2 text-sm font-medium">{{ $area }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('areas.index') }}"
                       class="inline-flex items-center gap-1.5 mt-4 text-brand font-bold text-sm hover:gap-2.5 transition-all">
                        كل مناطق الخدمة
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                </div>
            @endif

            <div>
                <h2 class="text-xl md:text-2xl font-extrabold mb-2">بنشتغل على كل الماركات</h2>
                <p class="text-muted leading-relaxed mb-4">إحنا مش توكيل لماركة بذاتها — فنيينا متدربين على أنواع وموديلات مختلفة. قولنا نوع جهازك وموديله وإحنا نظبّطك.</p>
                <a href="{{ route('brands.index') }}"
                   class="inline-flex items-center gap-1.5 text-brand font-bold text-sm hover:gap-2.5 transition-all">
                    شوف الماركات اللي بنتعامل معاها
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ============ فورم الحجز (بيحدد الخدمة تلقائياً) ============ --}}
    <section id="booking" class="bg-navy scroll-mt-20">
        <div class="mx-auto max-w-4xl px-4 py-16 md:py-20">
            <x-booking-form
                :heading="'احجز ' . $title"
                sub="قولنا بياناتك والمنطقة والوقت المناسب، وهنتواصل معاك نأكد المعاد والسعر التقريبي."
                :service="$bookingService"
                :show-appliance="false" />
        </div>
    </section>

@endsection
