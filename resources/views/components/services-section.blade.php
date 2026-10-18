@props([
    'section',               // مصفوفة القسم من config: id, title, subtitle, items[]
    'alt' => false,          // خلفية رمادية فاتحة للتبديل بين الأقسام
    'publishedSlugs' => [],  // slugs الخدمات المنشورة (عشان نعرض رابط التفاصيل للموجود بس)
])

<section id="{{ $section['id'] }}" class="scroll-mt-20 {{ $alt ? 'bg-surface' : '' }}">
    <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
        <div class="max-w-2xl mb-10">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-3">{{ $section['title'] }}</h2>
            <p class="text-muted text-lg">{{ $section['subtitle'] }}</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($section['items'] as $item)
                <article class="group flex flex-col rounded-2xl border border-line bg-white p-6 hover:border-brand hover:shadow-lg hover:shadow-brand/5 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4 group-hover:bg-brand group-hover:text-white transition-colors">
                        <x-service-icon :name="$item['icon']" />
                    </div>
                    <h3 class="text-lg font-bold mb-2">{{ $item['title'] }}</h3>
                    <p class="text-muted text-sm leading-relaxed mb-5 flex-1">{{ $item['desc'] }}</p>
                    @php $hasPage = ! empty($item['slug']) && in_array($item['slug'], $publishedSlugs, true); @endphp
                    <div class="flex items-center gap-4 mt-auto">
                        @if ($hasPage)
                            <a href="{{ '/services/' . $item['slug'] }}"
                               class="inline-flex items-center gap-1.5 text-brand font-bold text-sm hover:gap-2.5 transition-all">
                                التفاصيل
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                        @endif
                        <a href="#booking" data-service="{{ $item['booking'] }}"
                           class="inline-flex items-center gap-1.5 {{ $hasPage ? 'text-muted hover:text-brand' : 'text-brand' }} font-bold text-sm hover:gap-2.5 transition-all">
                            اطلب الخدمة
                            @unless ($hasPage)
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            @endunless
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
