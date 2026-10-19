@extends('layouts.app')

{{--
    معالج تشخيص العطل (wizard): العرض ← الاحتمالات ← التوصية + حجز.
    Progressive enhancement: من غير JS كل الأعراض وحلولها بتظهر كاملة (محتوى حقيقي
    للزوار ولمحركات البحث)، والـ JS بيحوّلها لمعالج خطوة بخطوة.
--}}

@php
    // بيانات التشخيص — صياغة أمينة: احتمالات مش تشخيص مؤكد، والتأكيد بالمعاينة.
    $symptoms = [
        [
            'id' => 'no-cooling', 'icon' => 'freon', 'label' => 'شغّال بس مش بيبرّد',
            'causes' => [
                'غاز الفريون ناقص أو فيه تسريب',
                'الفلتر متسخ وبيقفل مجرى الهوا',
                'الوحدة الخارجية متربة أو الكمبروسر مش بيشتغل صح',
                'درجة الحرارة مظبوطة غلط على الريموت',
            ],
            'service' => 'شحن فريون',
            'reco' => 'ابدأ بكشف ومعاينة. غالباً تنظيف وفحص غاز، ولو فيه نقص فريون بنشحنه بالكمية الصح.',
        ],
        [
            'id' => 'water-leak', 'icon' => 'cleaning', 'label' => 'بيقطّر مية من الوحدة الداخلية',
            'causes' => [
                'ماسورة الصرف مسدودة أو مايلة غلط',
                'حوض تجميع المية متسخ',
                'تجمّد ثلج على المبخّر بسبب نقص غاز أو فلتر متسخ',
            ],
            'service' => 'صيانة دورية',
            'reco' => 'محتاج تسليك للصرف وتنظيف وفحص عام. ننصح بصيانة دورية تكشف السبب وتمنع تكراره.',
        ],
        [
            'id' => 'noise', 'icon' => 'repair', 'label' => 'صوت عالي أو اهتزاز',
            'causes' => [
                'مروحة أو جلبة فيها مشكلة',
                'تثبيت الوحدة بقى مرتخي',
                'حاجة واقعة جوه الوحدة أو الموتور فيه عطل',
            ],
            'service' => 'إصلاح عطل',
            'reco' => 'الصوت لازم يتفحص على الطبيعة عشان نحدد مصدره بدقة. احجز كشف وإصلاح عطل.',
        ],
        [
            'id' => 'smell', 'icon' => 'cleaning', 'label' => 'ريحة وحشة من الهوا',
            'causes' => [
                'أتربة وبكتيريا متجمعة في الفلتر والمبخّر',
                'رطوبة وعفن جوه الوحدة',
                'مية راكدة في مجرى الصرف',
            ],
            'service' => 'غسيل وتنظيف',
            'reco' => 'الحل غالباً غسيل كامل بالبخار للوحدة والفلاتر. احجز غسيل وتنظيف.',
        ],
        [
            'id' => 'not-working', 'icon' => 'diagnose', 'label' => 'مش بيشتغل خالص',
            'causes' => [
                'مشكلة في الكهربا أو القاطع (الطبلون)',
                'الريموت أو لوحة التحكم',
                'كابل أو بوردة تحكم فيها عطل',
            ],
            'service' => 'إصلاح عطل',
            'reco' => 'محتاج كشف كهربائي وتشخيص قبل أي إصلاح. احجز إصلاح عطل ونشوف السبب.',
        ],
        [
            'id' => 'trips', 'icon' => 'repair', 'label' => 'بيفصل لوحده كل شوية',
            'causes' => [
                'حمل زيادة أو مشكلة في الكمبروسر',
                'الثرموستات بيفصل بدري',
                'سخونة في الوحدة الخارجية أو مشكلة كهربا',
            ],
            'service' => 'إصلاح عطل',
            'reco' => 'الفصل المتكرر لازم يتفحص بسرعة عشان ما يأثرش على الكمبروسر. احجز كشف وإصلاح عطل.',
        ],
        [
            'id' => 'install-move', 'icon' => 'install', 'label' => 'عايز أركّب أو أنقل تكييف',
            'causesLabel' => 'اللي ممكن تحتاجه',
            'causes' => [
                'تركيب وحدة جديدة مع تأسيس المواسير',
                'فك الوحدة ونقلها لمكان تاني',
                'إعادة تركيب وتجربة تشغيل كاملة',
            ],
            'service' => 'تركيب تكييف',
            'reco' => 'ده مش عطل — ده شغل تركيب أو نقل. احجز تركيب تكييف أو فك ونقل حسب اللي محتاجه.',
        ],
    ];
@endphp

@section('content')
    <x-page-hero
        title="شخّص عطل تكييفك"
        subtitle="مش عارف المشكلة فين؟ اختار العرض اللي بيحصل، وهنوريك الأسباب المحتملة والخدمة المناسبة — وتقدر تحجز على طول."
        :breadcrumbs="$breadcrumbs" />

    {{-- بدون JS: نظهّر كل النتايج ونخفي أزرار المعالج --}}
    <noscript>
        <style>
            [data-result], [data-sub-reco] { display: block !important; }
            [data-show-reco], [data-reset] { display: none !important; }
        </style>
    </noscript>

    <section class="mx-auto max-w-3xl px-4 pb-16 md:pb-20">
        <div data-diagnose class="scroll-mt-24">

            {{-- شريط التقدّم --}}
            <ol data-progress class="flex items-center justify-between gap-2 mb-8 text-sm" aria-label="خطوات التشخيص">
                @foreach (['العرض', 'الاحتمالات', 'التوصية'] as $i => $label)
                    <li data-pstep="{{ $i + 1 }}" class="diag-step flex items-center gap-2 flex-1 {{ $i === 0 ? 'is-active' : '' }}">
                        <span class="diag-num w-8 h-8 rounded-full border border-line bg-white flex items-center justify-center font-bold shrink-0">{{ $i + 1 }}</span>
                        <span class="font-semibold">{{ $label }}</span>
                        @if ($i < 2)<span class="hidden sm:block flex-1 h-px bg-line" aria-hidden="true"></span>@endif
                    </li>
                @endforeach
            </ol>

            {{-- الخطوة ١: اختيار العرض --}}
            <div data-step1>
                <h2 class="text-xl md:text-2xl font-extrabold mb-5">اختار العرض اللي بيحصل</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($symptoms as $s)
                        <button type="button" data-go="{{ $s['id'] }}"
                                class="group flex items-center gap-3 text-start rounded-2xl border border-line bg-white p-4 hover:border-brand hover:shadow-md hover:shadow-brand/5 transition-all">
                            <span class="w-11 h-11 rounded-xl bg-brand-50 text-brand flex items-center justify-center shrink-0 group-hover:bg-brand group-hover:text-white transition-colors">
                                <x-service-icon :name="$s['icon']" class="w-6 h-6" />
                            </span>
                            <span class="font-bold">{{ $s['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- النتايج (الخطوة ٢ + ٣ لكل عرض) --}}
            @foreach ($symptoms as $s)
                <div data-result="{{ $s['id'] }}" hidden
                     class="rounded-3xl border border-line bg-white p-6 md:p-8">
                    <button type="button" data-reset
                            class="inline-flex items-center gap-1.5 text-muted hover:text-brand font-semibold text-sm mb-5 transition-colors">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        غيّر العرض
                    </button>

                    <p class="text-sm text-muted mb-1">العرض اللي اخترته</p>
                    <h2 class="text-xl md:text-2xl font-extrabold mb-6">{{ $s['label'] }}</h2>

                    {{-- الخطوة ٢: الاحتمالات --}}
                    <div data-sub-causes>
                        <h3 class="font-bold text-lg mb-3">{{ $s['causesLabel'] ?? 'الأسباب المحتملة' }}</h3>
                        <ul class="space-y-2.5 mb-6">
                            @foreach ($s['causes'] as $cause)
                                <li class="flex items-start gap-2.5">
                                    <span class="w-2 h-2 rounded-full bg-brand mt-2 shrink-0" aria-hidden="true"></span>
                                    <span class="leading-relaxed">{{ $cause }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <button type="button" data-show-reco
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full border border-brand text-brand font-bold hover:bg-brand hover:text-white transition-colors">
                            شوف التوصية
                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                    </div>

                    {{-- الخطوة ٣: التوصية + الحجز --}}
                    <div data-sub-reco hidden class="mt-7 pt-7 border-t border-line">
                        <h3 class="font-bold text-lg mb-2">التوصية</h3>
                        <p class="text-ink/90 leading-relaxed mb-2">{{ $s['reco'] }}</p>
                        <p class="text-sm text-muted mb-5">* دي احتمالات للاسترشاد — التأكيد النهائي بيكون بالمعاينة على الطبيعة.</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('request-service', ['service' => $s['service']]) }}"
                               class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                                احجز: {{ $s['service'] }}
                            </a>
                            <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
                               class="inline-flex items-center px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-bold transition-colors">
                                اسأل فني على واتساب
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </section>
@endsection
