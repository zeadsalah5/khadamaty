@props(['publishedSlugs' => []])

@php
    // العنوان/الوصف من الداشبورد (قابل للتعديل)، والبطاقات من الخدمات المنشورة الحقيقية
    // (نفس خدمات صفحة /services بنفس الترتيب) — فتعديل أي خدمة ينعكس هنا تلقائياً.
    $vs       = (array) site('visual_services');
    $services = \App\Models\Service::query()->published()->orderByDesc('updated_at')->get();
@endphp

@if ($services->isNotEmpty())
<section class="bg-surface scroll-mt-20">
    <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
        <div class="max-w-2xl mb-10">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-3">{{ $vs['title'] ?? 'خدماتنا' }}</h2>
            @if (filled($vs['subtitle'] ?? null))
                <p class="text-muted text-lg">{{ $vs['subtitle'] }}</p>
            @endif
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <article class="group overflow-hidden rounded-2xl bg-white border border-line hover:border-brand hover:shadow-xl hover:shadow-brand/5 transition-all">
                    <a href="/services/{{ $service->slug }}" class="block focus-visible:outline-none">
                        <div class="relative aspect-[16/10] overflow-hidden bg-surface">
                            @if ($service->imageUrl())
                                {{-- صورة الخدمة المرفوعة من الداشبورد --}}
                                <img src="{{ $service->imageUrl() }}" alt="{{ $service->name_ar }}"
                                     width="640" height="400" loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                {{-- fallback: أيقونة الخدمة فوق تدرّج بارد --}}
                                <div class="w-full h-full bg-gradient-to-br from-navy via-[#1E3A5F] to-[#16343a] flex items-center justify-center text-white/90" aria-hidden="true">
                                    <x-service-icon :name="$service->icon ?: 'maintenance'" class="w-12 h-12" />
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold mb-1.5 group-hover:text-brand transition-colors">{{ $service->name_ar }}</h3>
                            @if (filled($service->excerpt))
                                <p class="text-muted text-sm leading-relaxed">{{ $service->excerpt }}</p>
                            @endif
                            <span class="inline-flex items-center gap-1.5 mt-4 text-brand font-bold text-sm">
                                التفاصيل
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
