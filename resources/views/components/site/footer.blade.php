<footer class="bg-navy text-white mt-20">
    <div class="mx-auto max-w-6xl px-4 py-14 grid gap-10 md:grid-cols-4">
        <div class="md:col-span-2">
            <x-logo variant="light" />
            <p class="mt-4 text-sm text-white/70 max-w-sm leading-relaxed">
                {{ site('tagline') }}. بنخدم {{ site('areas_label') }}
                بفنيين متخصصين ومواعيد مرنة والدفع بعد الشغل.
            </p>
            @if (\App\Support\Site::hasWhatsapp() || \App\Support\Site::hasPhone())
            <div class="mt-5 flex flex-wrap gap-3">
                @if (\App\Support\Site::hasWhatsapp())
                    <a href="https://wa.me/{{ \App\Support\Site::whatsappNumber() }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-accent hover:bg-accent-600 text-sm font-semibold transition-colors">
                        واتساب
                    </a>
                @endif
                @if (\App\Support\Site::hasPhone())
                    <a href="tel:{{ \App\Support\Site::phoneTel() ?? \App\Support\Site::phoneDisplay() }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/25 hover:border-white text-sm font-semibold transition-colors">
                        {{ \App\Support\Site::phoneDisplay() }}
                    </a>
                @endif
            </div>
            @endif

            @if (filled(site('working_hours')))
                <p class="mt-4 text-sm text-white/60">{{ site('working_hours') }}</p>
            @endif

            @php $socialLinks = array_filter((array) site('social_links', []), fn ($l) => ! empty($l['url'])); @endphp
            @if (! empty($socialLinks))
                <ul class="mt-4 flex flex-wrap gap-3 text-sm">
                    @foreach ($socialLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                               class="text-white/70 hover:text-white underline underline-offset-4">{{ $link['label'] ?? $link['url'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <h3 class="font-bold mb-4">روابط سريعة</h3>
            @php
                $footerMenu = \App\Support\Catalog::menu('footer');
                if (empty($footerMenu)) {
                    $footerMenu = [
                        ['label' => 'الخدمات', 'url' => route('services.index')],
                        ['label' => 'المناطق', 'url' => route('areas.index')],
                        ['label' => 'الماركات', 'url' => route('brands.index')],
                        ['label' => 'الأسعار', 'url' => route('prices')],
                        ['label' => 'مين إحنا', 'url' => route('about')],
                        ['label' => 'الأسئلة الشائعة', 'url' => route('faq')],
                        ['label' => 'شخّص عطلك', 'url' => route('diagnose')],
                        ['label' => 'آراء العملاء', 'url' => route('reviews')],
                        ['label' => 'اتصل بينا', 'url' => route('contact')],
                        ['label' => 'اطلب خدمة', 'url' => route('request-service')],
                    ];
                }
                $footerMenu = array_map(function ($i) {
                    $i['url'] = \Illuminate\Support\Str::startsWith($i['url'], ['http://', 'https://', '#', 'mailto:', 'tel:', '//'])
                        ? $i['url'] : url($i['url']);
                    return $i;
                }, $footerMenu);
                // صفحات ثابتة منشورة (سياسات...) بتظهر في الفوتر.
                $footerPages = \App\Support\ContentCache::remember('footer.pages', fn () =>
                    \App\Models\Page::query()->where('is_published', true)
                        ->where('show_in_footer', true)->orderBy('title')
                        ->get(['slug', 'title'])->all());
            @endphp
            <ul class="space-y-2.5 text-sm text-white/70">
                @foreach ($footerMenu as $item)
                    <li><a href="{{ $item['url'] }}" class="hover:text-white">{{ $item['label'] }}</a></li>
                @endforeach
                @foreach ($footerPages as $p)
                    <li><a href="{{ '/pages/' . $p->slug }}" class="hover:text-white">{{ $p->title }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="font-bold mb-4">مناطق الخدمة</h3>
            @php $footerAreas = \App\Models\Area::query()->published()->orderBy('name_ar')->take(6)->get(); @endphp
            <ul class="space-y-2.5 text-sm text-white/70">
                @if ($footerAreas->isNotEmpty())
                    @foreach ($footerAreas as $area)
                        <li><a href="{{ '/areas/' . $area->slug }}" class="hover:text-white">{{ $area->name_ar }}</a></li>
                    @endforeach
                    <li><a href="{{ route('areas.index') }}" class="text-white/90 hover:text-white font-semibold">كل المناطق</a></li>
                @else
                    @foreach (array_slice(site('areas'), 0, 6) as $area)
                        <li>{{ $area }}</li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto max-w-6xl px-4 py-5 text-center text-xs text-white/55">
            © {{ date('Y') }} {{ site('name') }} — جميع الحقوق محفوظة.
        </div>
    </div>
</footer>
