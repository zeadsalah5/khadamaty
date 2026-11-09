@php
    use Illuminate\Support\Facades\Storage;

    $a    = (array) site('about_home');
    $disk = Storage::disk('sections');
    $img  = $a['image'] ?? null;
    $imgUrl = ($img && $disk->exists($img)) ? $disk->url($img) : null;

    $points  = array_values(array_filter((array) ($a['points'] ?? []), fn ($p) => filled($p)));
    $ctaHref = $a['cta_href'] ?? '/about';
@endphp

@if (filled($a['title'] ?? null))
<section class="scroll-mt-20">
    <div class="mx-auto max-w-6xl px-4 py-16 md:py-20 grid md:grid-cols-2 gap-10 lg:gap-14 items-center">
        {{-- الصورة (على الشمال في الديسكتوب) --}}
        <div class="order-1 md:order-2">
            @if ($imgUrl)
                <img src="{{ $imgUrl }}" width="1200" height="900" loading="lazy" decoding="async"
                     alt="{{ $a['image_alt'] ?: $a['title'] }}"
                     class="w-full rounded-3xl object-cover aspect-[4/3] shadow-xl shadow-navy/10 ring-1 ring-line">
            @else
                {{-- fallback تدرّج بارد بدل الصورة --}}
                <div class="w-full rounded-3xl aspect-[4/3] bg-gradient-to-br from-navy via-[#1E3A5F] to-[#16343a] flex items-center justify-center text-white/90 ring-1 ring-white/10"
                     aria-hidden="true">
                    <x-service-icon name="install" class="w-16 h-16" />
                </div>
            @endif
        </div>

        {{-- النص (على اليمين في الديسكتوب — بداية السطر RTL) --}}
        <div class="order-2 md:order-1">
            @if (filled($a['eyebrow'] ?? null))
                <span class="inline-block text-brand font-bold text-sm mb-3">{{ $a['eyebrow'] }}</span>
            @endif
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-balance">{{ $a['title'] }}</h2>
            @if (filled($a['body'] ?? null))
                <p class="text-muted text-lg leading-relaxed mb-6">{{ $a['body'] }}</p>
            @endif

            @if ($points)
                <ul class="space-y-3 mb-8">
                    @foreach ($points as $point)
                        <li class="flex items-start gap-3">
                            <span class="shrink-0 mt-0.5 w-6 h-6 rounded-full bg-accent/15 text-accent flex items-center justify-center" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="font-semibold">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if (filled($a['cta_label'] ?? null))
                <a href="{{ $ctaHref }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-navy hover:bg-navy-700 text-white font-bold transition-colors">
                    {{ $a['cta_label'] }}
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            @endif
        </div>
    </div>
</section>
@endif
