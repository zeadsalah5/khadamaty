@php
    use Illuminate\Support\Facades\Storage;

    // المحتوى من الداشبورد (settings) مع fallback تلقائي لـ config/site.php لأي حقل جديد.
    $slides    = (array) site('hero_slides');
    $cfgSlides = (array) config('site.hero_slides', []);

    $areasCount  = \App\Models\Area::query()->published()->count();
    $hasWarranty = filled(site('warranty_scope')) && filled(site('warranty_exclusions'));
    $heroDisk    = Storage::disk('hero');

    // نجهّز بيانات كل شريحة (fallback للكونفيج + تجهيز الصورة + فلترة الكروت).
    $prepared = [];
    foreach ($slides as $index => $slide) {
        if (array_key_exists('enabled', $slide) && ! $slide['enabled']) {
            continue; // شريحة متوقّفة من الداشبورد
        }
        $cfg = $cfgSlides[$index] ?? [];
        $get = fn (string $key, $fallback = null) => $slide[$key] ?? $cfg[$key] ?? $fallback;

        $preset = (int) $get('gradient_preset', 1);
        $preset = in_array($preset, [1, 2, 3], true) ? $preset : 1;

        // صورة الخلفية (WebP بمقاسين) لو موجودة.
        $bg = $get('background_image');
        $bgUrl = $bgSrcset = null;
        if ($bg && $heroDisk->exists($bg)) {
            $bgUrl   = $heroDisk->url($bg);
            $small   = preg_replace('/\.webp$/', '-960.webp', $bg);
            $bgSrcset = $heroDisk->exists($small)
                ? $heroDisk->url($small) . ' 960w, ' . $bgUrl . ' 1920w'
                : null;
        }

        $focal = $get('focal', 'center');
        $overlay = $get('overlay_strength', 'medium');

        $features = collect((array) ($get('features') ?? []))
            ->filter(fn ($f) => filled($f['title'] ?? null))
            ->filter(fn ($f) => ($f['requires'] ?? null) !== 'warranty' || $hasWarranty)
            ->map(fn ($f) => [
                'icon'  => $f['icon'] ?? null,
                'title' => str_replace('{areas_count}', (string) $areasCount, (string) $f['title']),
                'text'  => str_replace('{areas_count}', (string) $areasCount, (string) ($f['text'] ?? '')),
            ])
            ->take(3)->values();

        $prepared[] = [
            'eyebrow'    => $get('eyebrow') ?? $get('badge'),
            'titleLines' => array_slice((array) ($get('title_lines') ?? []), 0, 2),
            'subtitle'   => $get('subtitle') ?? $get('text'),
            'ctaLabel'   => $get('cta_label', 'احجز فني دلوقتي'),
            'ctaHref'    => $get('cta_href', '#booking'),
            'ctaService' => $get('cta_service'),
            'preset'     => $preset,
            'overlay'    => in_array($overlay, ['light', 'medium', 'strong'], true) ? $overlay : 'medium',
            'focal'      => in_array($focal, ['right', 'center', 'left'], true) ? $focal : 'center',
            'bgUrl'      => $bgUrl,
            'bgAlt'      => (string) ($get('background_alt') ?? ''),
            'bgSrcset'   => $bgSrcset,
            'hasImage'   => (bool) $bgUrl,
            'features'   => $features,
        ];
    }

    $count = count($prepared);
    $hasWhatsapp = \App\Support\Site::hasWhatsapp();
    $hasPhone    = \App\Support\Site::hasPhone();
@endphp

@if ($count)
<section class="hero hero-slider" data-hero-slider
         aria-roledescription="عرض متحرّك" aria-label="عروض خدمات خدماتي">
    <div class="hero-slides" data-hero-slides>
        @foreach ($prepared as $i => $s)
            <article class="hero-slide hero--{{ $s['hasImage'] ? 'image' : 'art' }} {{ $i === 0 ? 'is-active' : '' }}"
                     data-hero-slide role="group" aria-roledescription="شريحة"
                     aria-label="شريحة {{ $i + 1 }} من {{ $count }}"
                     @if ($i !== 0) aria-hidden="true" inert @endif>

                {{-- الخلفية: صورة cover أو تدرّج بارد --}}
                <div class="hero__bg hero__bg--preset-{{ $s['preset'] }}" aria-hidden="true">
                    @if ($s['bgUrl'])
                        <img class="hero__img" src="{{ $s['bgUrl'] }}" width="1920" height="1080"
                             @if ($s['bgSrcset']) srcset="{{ $s['bgSrcset'] }}" sizes="100vw" @endif
                             alt="{{ $s['bgAlt'] }}"
                             loading="{{ $i === 0 ? 'eager' : 'lazy' }}" @if ($i === 0) fetchpriority="high" @endif
                             decoding="async"
                             style="object-position: {{ $s['focal'] === 'right' ? 'right' : ($s['focal'] === 'left' ? 'left' : 'center') }} top;">
                    @endif
                </div>

                <div class="hero__scrim hero-overlay--{{ $s['overlay'] }}" aria-hidden="true"></div>

                <div class="hero__inner">
                    <div class="hero__main">
                        <div class="hero__content">
                            @if ($s['eyebrow'])
                                <span class="hero__eyebrow">{{ $s['eyebrow'] }}</span>
                            @endif

                            @php $tag = $i === 0 ? 'h1' : 'h2'; @endphp
                            <{{ $tag }} class="hero__title">
                                @forelse ($s['titleLines'] as $line)
                                    {{ $line }}@if (! $loop->last)<br>@endif
                                @empty
                                    {{ site('name') }}
                                @endforelse
                            </{{ $tag }}>

                            @if ($s['subtitle'])
                                <p class="hero__lead">{{ $s['subtitle'] }}</p>
                            @endif

                            <div class="hero__cta">
                                {{-- زرارين قصاد بعض: واتساب + اتصال. بيظهروا لما الرقم متسجّل من الداشبورد؛
                                     لو الاتنين فاضيين بيظهر زر «احجز» كبديل (من غير روابط ميتة). --}}
                                @if ($hasWhatsapp)
                                    <a href="https://wa.me/{{ \App\Support\Site::whatsappNumber() }}" target="_blank" rel="noopener"
                                       class="hero__btn hero__btn--whatsapp">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" aria-hidden="true"><path d="M12 2a9.9 9.9 0 0 0-8.5 15l-1.3 4.8 4.9-1.3A10 10 0 1 0 12 2Zm5.8 14.2c-.2.7-1.4 1.3-2 1.4-.5.1-1.2.1-1.9-.1-.4-.1-1-.3-1.8-.6-3-1.3-5-4.4-5.1-4.6-.2-.2-1.3-1.7-1.3-3.2S6.5 7 6.8 6.7a1 1 0 0 1 .7-.3h.5c.2 0 .4 0 .6.5l.8 1.9c.1.2.1.4 0 .6l-.4.5c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.6-.1l.7-.8c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.6.3.1.3.1.6-.1 1.3Z"/></svg>
                                        واتساب
                                    </a>
                                @endif
                                @if ($hasPhone)
                                    <a href="tel:{{ \App\Support\Site::phoneTel() ?? \App\Support\Site::phoneDisplay() }}"
                                       class="hero__btn hero__btn--call">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.3.2 2.5.57 3.6a1 1 0 0 1-.25 1l-2.2 2.2Z"/></svg>
                                        اتصل بينا
                                    </a>
                                @endif
                                @unless ($hasWhatsapp || $hasPhone)
                                    <a href="{{ $s['ctaHref'] }}" @if ($s['ctaService']) data-service="{{ $s['ctaService'] }}" @endif
                                       class="hero__btn hero__btn--primary">{{ $s['ctaLabel'] }}</a>
                                @endunless
                            </div>
                        </div>

                        @unless ($s['hasImage'])
                            {{-- رسمة أصلية (fallback بدون صورة): وحدة تكييف سبليت + هوا بارد --}}
                            <div class="hero__art" aria-hidden="true">
                                <svg class="hero-ac" viewBox="0 0 360 340" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <linearGradient id="acBody{{ $i }}" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0" stop-color="#ffffff"/>
                                            <stop offset="1" stop-color="#e9eef5"/>
                                        </linearGradient>
                                    </defs>
                                    <ellipse cx="180" cy="150" rx="150" ry="16" fill="#000000" opacity="0.12"/>
                                    <rect x="34" y="46" width="292" height="96" rx="22" fill="url(#acBody{{ $i }})"/>
                                    <rect x="34" y="46" width="292" height="96" rx="22" stroke="#ffffff" stroke-opacity="0.6" stroke-width="2"/>
                                    <rect x="58" y="66" width="200" height="6" rx="3" fill="#cfd8e6"/>
                                    <rect x="58" y="78" width="150" height="6" rx="3" fill="#dde4ee"/>
                                    <circle cx="300" cy="72" r="6" fill="var(--color-accent)"/>
                                    <rect x="270" y="92" width="40" height="16" rx="5" fill="#eef2f8"/>
                                    <path d="M278 100h8M292 100h10" stroke="#9fb0c6" stroke-width="3" stroke-linecap="round"/>
                                    <rect x="46" y="120" width="268" height="14" rx="7" fill="#d7deea"/>
                                    <g stroke="#bfe9ff" stroke-width="4" stroke-linecap="round" opacity="0.85">
                                        <path class="hero-ac__stream hero-ac__stream--1" d="M96 150c0 26-14 30-14 56s14 30 14 56"/>
                                        <path class="hero-ac__stream hero-ac__stream--2" d="M150 150c0 30-16 34-16 64s16 34 16 64"/>
                                        <path class="hero-ac__stream hero-ac__stream--3" d="M210 150c0 26-14 30-14 56s14 30 14 56"/>
                                        <path class="hero-ac__stream hero-ac__stream--4" d="M264 150c0 30-16 34-16 64s16 34 16 64"/>
                                    </g>
                                    <g fill="#eaf7ff">
                                        <circle class="hero-ac__dot hero-ac__dot--1" cx="120" cy="250" r="4"/>
                                        <circle class="hero-ac__dot hero-ac__dot--2" cx="186" cy="276" r="5"/>
                                        <circle class="hero-ac__dot hero-ac__dot--3" cx="242" cy="244" r="4"/>
                                    </g>
                                </svg>
                            </div>
                        @endunless
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if ($count > 1)
        {{-- أسهم (RTL: الشمال = التالي، اليمين = السابق) — أزرار حقيقية --}}
        <button type="button" class="hero-arrow hero-arrow--next" data-hero-next aria-label="الشريحة التالية">
            <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
        </button>
        <button type="button" class="hero-arrow hero-arrow--prev" data-hero-prev aria-label="الشريحة السابقة">
            <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
        </button>

        {{-- تشغيل/إيقاف + نقط الترقيم --}}
        <div class="hero-controls">
            <button type="button" class="hero-playpause" data-hero-playpause aria-label="إيقاف التبديل التلقائي">
                <svg class="icon-pause w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                <svg class="icon-play w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
            </button>
            <div class="hero-dots" role="group" aria-label="اختيار الشريحة">
                @foreach ($prepared as $i => $s)
                    <button type="button" class="hero-dot {{ $i === 0 ? 'is-active' : '' }}"
                            data-hero-dot aria-label="الشريحة {{ $i + 1 }}"
                            @if ($i === 0) aria-current="true" @endif></button>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endif
