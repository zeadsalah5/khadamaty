@props(['publishedSlugs' => []])

@php
    use Illuminate\Support\Facades\Storage;

    $vs    = (array) site('visual_services');
    $items = array_values(array_filter((array) ($vs['items'] ?? []), fn ($i) => filled($i['title'] ?? null)));
    $disk  = Storage::disk('sections');
@endphp

@if ($items)
<section class="bg-surface scroll-mt-20">
    <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
        <div class="max-w-2xl mb-10">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-3">{{ $vs['title'] ?? 'خدماتنا' }}</h2>
            @if (filled($vs['subtitle'] ?? null))
                <p class="text-muted text-lg">{{ $vs['subtitle'] }}</p>
            @endif
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($items as $item)
                @php
                    $img     = $item['image'] ?? null;
                    $imgUrl  = ($img && $disk->exists($img)) ? $disk->url($img) : null;
                    $hasPage = ! empty($item['slug']) && in_array($item['slug'], $publishedSlugs, true);
                    $href    = $hasPage ? '/services/' . $item['slug'] : '#booking';
                @endphp
                <article class="group overflow-hidden rounded-2xl bg-white border border-line hover:border-brand hover:shadow-xl hover:shadow-brand/5 transition-all">
                    <a href="{{ $href }}" @unless ($hasPage) data-service="{{ $item['booking'] ?? '' }}" @endunless
                       class="block focus-visible:outline-none">
                        <div class="relative aspect-[16/10] overflow-hidden bg-surface">
                            @if ($imgUrl)
                                <img src="{{ $imgUrl }}" width="1200" height="750" loading="lazy" decoding="async"
                                     alt="{{ $item['image_alt'] ?: $item['title'] }}"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.04]">
                            @else
                                {{-- fallback: أيقونة الخدمة فوق تدرّج بارد لحد ما الصورة تترفع --}}
                                <div class="w-full h-full bg-gradient-to-br from-navy via-[#1E3A5F] to-[#16343a] flex items-center justify-center text-white/90" aria-hidden="true">
                                    <x-service-icon :name="$item['icon'] ?? 'maintenance'" class="w-12 h-12" />
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold mb-1.5 group-hover:text-brand transition-colors">{{ $item['title'] }}</h3>
                            @if (filled($item['desc'] ?? null))
                                <p class="text-muted text-sm leading-relaxed">{{ $item['desc'] }}</p>
                            @endif
                            <span class="inline-flex items-center gap-1.5 mt-4 text-brand font-bold text-sm">
                                {{ $hasPage ? 'التفاصيل' : 'اطلب الخدمة' }}
                                <svg viewBox="0 0 24 24" class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </span>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
