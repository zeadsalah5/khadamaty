@php
    $slides = site('hero_slides');
    $area   = site('areas_label');
@endphp

<section class="relative bg-brand text-white overflow-hidden" data-slider aria-roledescription="سلايدر" aria-label="عروض الخدمات">
    <div class="slider-viewport overflow-hidden">
        <div class="slider-track flex" data-slider-track>
            @foreach ($slides as $index => $slide)
                @php
                    $imgRel    = $slide['image'] ? 'images/technicians/' . $slide['image'] : null;
                    $imgExists = $imgRel && file_exists(public_path($imgRel));
                @endphp
                <div class="slider-slide shrink-0 w-full" role="group" aria-roledescription="شريحة"
                     aria-label="{{ $index + 1 }} من {{ count($slides) }}" @if(!$loop->first) aria-hidden="true" @endif>
                    <div class="mx-auto max-w-6xl px-4 pt-14 pb-24 md:pt-20 md:pb-32 grid md:grid-cols-2 gap-10 items-center">
                        {{-- النص --}}
                        <div class="text-center md:text-right">
                            <p class="inline-flex items-center gap-2 bg-white/15 rounded-full px-3.5 py-1.5 text-sm font-medium mb-5">
                                {{ $slide['badge'] }} · {{ $area }}
                            </p>
                            {{-- H1 للشريحة الأولى بس (SEO: عنوان واحد للصفحة)؛ الباقي نص بنفس الشكل --}}
                            @php $headingTag = $loop->first ? 'h1' : 'p'; @endphp
                            <{{ $headingTag }} class="text-4xl md:text-6xl font-extrabold leading-[1.15] mb-5">
                                @foreach ($slide['title_lines'] as $line)
                                    {{ $line }}@if(!$loop->last)<br>@endif
                                @endforeach
                            </{{ $headingTag }}>
                            <p class="text-lg md:text-xl text-white/90 leading-relaxed mb-8 max-w-lg md:ms-auto">
                                {{ $slide['text'] }}
                            </p>
                            <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                                <a href="{{ $slide['cta_href'] }}"
                                   @isset($slide['cta_service']) data-service="{{ $slide['cta_service'] }}" @endisset
                                   class="inline-flex items-center px-7 py-3.5 rounded-full bg-white text-brand font-extrabold text-lg shadow-lg shadow-black/10 hover:-translate-y-0.5 transition-transform">
                                    {{ $slide['cta_label'] }}
                                </a>
                                <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full border border-white/40 text-white font-bold text-lg hover:bg-white/10 transition-colors">
                                    كلّمنا واتساب
                                </a>
                            </div>
                        </div>

                        {{-- الوسائط: رسم أو صورة فني أو placeholder --}}
                        <div class="relative flex justify-center md:justify-start">
                            @if ($slide['type'] === 'illustration')
                                <svg viewBox="0 0 320 240" class="w-full max-w-sm drop-shadow-2xl" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="رسم توضيحي لوحدة تكييف">
                                    <rect x="30" y="40" width="260" height="78" rx="16" fill="#ffffff"/>
                                    <rect x="30" y="92" width="260" height="26" fill="#eef1f5"/>
                                    <path d="M30 104h260" stroke="#d9dee6" stroke-width="2"/>
                                    <rect x="52" y="60" width="150" height="7" rx="3.5" fill="#cfd6e0"/>
                                    <rect x="52" y="74" width="110" height="7" rx="3.5" fill="#e2e7ee"/>
                                    <circle cx="262" cy="73" r="7" fill="var(--color-accent)"/>
                                    <path d="M80 150c0 14 14 14 14 28" stroke="#ffffff" stroke-width="5" stroke-linecap="round" opacity=".9"/>
                                    <path d="M130 150c0 16 16 16 16 32" stroke="#ffffff" stroke-width="5" stroke-linecap="round" opacity=".7"/>
                                    <path d="M180 150c0 14 14 14 14 28" stroke="#ffffff" stroke-width="5" stroke-linecap="round" opacity=".55"/>
                                    <path d="M230 150c0 16 16 16 16 32" stroke="#ffffff" stroke-width="5" stroke-linecap="round" opacity=".4"/>
                                </svg>
                            @elseif ($imgExists)
                                {{-- أبعاد ثابتة لتفادي القفز (CLS) --}}
                                <img src="{{ asset($imgRel) }}" width="440" height="360"
                                     loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async"
                                     alt="فني {{ site('name') }} أثناء العمل"
                                     class="w-full max-w-sm rounded-3xl object-cover shadow-2xl aspect-[11/9] bg-white/10">
                            @else
                                {{-- TODO: ضع صورة الفني في public/images/technicians/{{ $slide['image'] }} وهتظهر تلقائياً --}}
                                <div class="w-full max-w-sm aspect-[11/9] rounded-3xl border-2 border-dashed border-white/50 bg-white/10 flex flex-col items-center justify-center text-center gap-2 shadow-2xl">
                                    <svg viewBox="0 0 24 24" class="w-12 h-12 text-white/70" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m4 18 5-4 4 3 3-2 4 3"/>
                                    </svg>
                                    <span class="font-bold">صورة الفني هنا</span>
                                    <span class="text-xs text-white/70 px-4">ضع {{ $slide['image'] }} في مجلد الصور</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if (count($slides) > 1)
        {{-- أسهم التنقل (RTL: الشمال = التالي، اليمين = السابق) --}}
        <button type="button" data-slider-next aria-label="الشريحة التالية"
                class="hidden sm:flex absolute top-1/2 start-3 md:start-5 -translate-y-1/2 z-10 w-11 h-11 rounded-full bg-white/20 hover:bg-white/35 backdrop-blur items-center justify-center transition-colors">
            <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
        </button>
        <button type="button" data-slider-prev aria-label="الشريحة السابقة"
                class="hidden sm:flex absolute top-1/2 end-3 md:end-5 -translate-y-1/2 z-10 w-11 h-11 rounded-full bg-white/20 hover:bg-white/35 backdrop-blur items-center justify-center transition-colors">
            <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
        </button>

        {{-- نقط الترقيم --}}
        <div class="absolute bottom-16 md:bottom-20 inset-x-0 flex justify-center gap-2.5 z-10" data-slider-dots role="tablist" aria-label="اختيار الشريحة">
            @foreach ($slides as $index => $slide)
                <button type="button" data-slider-dot role="tab"
                        aria-label="الشريحة {{ $index + 1 }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                        class="slider-dot w-2.5 h-2.5 rounded-full bg-white/50 transition-all"></button>
            @endforeach
        </div>
    @endif

    {{-- القطع المائل الأبيض --}}
    <svg class="hero-cut" viewBox="0 0 1440 90" preserveAspectRatio="none" fill="#ffffff" aria-hidden="true">
        <path d="M0 90L1440 0V90H0Z"/>
    </svg>
</section>
