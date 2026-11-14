@extends('layouts.app')

{{-- عمل مفرد في المعرض. متغيرات: $project, $related, $breadcrumbs --}}

@section('content')
    <x-breadcrumbs :items="$breadcrumbs" />

    <header class="mx-auto max-w-5xl px-4 pt-6 pb-6">
        <div class="flex flex-wrap gap-2 mb-3">
            @if ($project->service_type)
                <span class="rounded-full bg-brand-50 text-brand text-sm font-bold px-3 py-1">{{ $project->service_type }}</span>
            @endif
            @if ($project->area_name)
                <span class="rounded-full bg-surface border border-line text-muted text-sm font-medium px-3 py-1">{{ $project->area_name }}</span>
            @endif
            @if ($project->completed_at)
                <span class="rounded-full bg-surface border border-line text-muted text-sm font-medium px-3 py-1">{{ $project->completed_at->translatedFormat('F Y') }}</span>
            @endif
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold mb-3">{{ $project->title }}</h1>
        @if ($project->summary)
            <p class="text-muted text-lg leading-relaxed max-w-3xl">{{ $project->summary }}</p>
        @endif
    </header>

    {{-- الغلاف --}}
    @if ($project->coverUrl())
        <section class="mx-auto max-w-5xl px-4 pb-6">
            <div class="overflow-hidden rounded-3xl border border-line bg-surface">
                <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}"
                     width="1280" height="720" loading="eager" fetchpriority="high"
                     class="w-full h-auto aspect-[16/9] object-cover">
            </div>
        </section>
    @endif

    {{-- التفاصيل --}}
    @if (filled($project->body))
        <section class="mx-auto max-w-3xl px-4 pb-6">
            <div class="space-y-4 text-lg leading-loose text-ink/90">
                @foreach (preg_split('/\n\s*\n/', trim($project->body)) as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>
        </section>
    @endif

    {{-- معرض الصور --}}
    @php $gallery = $project->galleryUrls(); @endphp
    @if (! empty($gallery))
        <section class="mx-auto max-w-5xl px-4 py-6">
            <h2 class="text-2xl font-extrabold mb-5">صور من الشغل</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gallery as $i => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener"
                       class="block aspect-square overflow-hidden rounded-2xl border border-line bg-surface group">
                        <img src="{{ $url }}" alt="{{ $project->title }} — صورة {{ $i + 1 }}"
                             width="500" height="500" loading="lazy"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="bg-navy scroll-mt-20 mt-6">
        <div class="mx-auto max-w-4xl px-4 py-14 md:py-16 text-center">
            <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-3">عايز خدمة زي دي؟</h2>
            <p class="text-white/70 leading-relaxed mb-6">خبرة أكتر من {{ (int) site('experience_years', 15) }} سنة في خدمتك. احجز معاينة أو كلّمنا.</p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('request-service') }}" class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">احجز فني</a>
                @if (\App\Support\Site::hasWhatsapp())
                    <a href="https://wa.me/{{ \App\Support\Site::whatsappNumber() }}" target="_blank" rel="noopener" class="inline-flex items-center px-6 py-3 rounded-full bg-accent hover:bg-accent-600 text-white font-bold transition-colors">واتساب</a>
                @endif
            </div>
        </div>
    </section>

    {{-- أعمال أخرى --}}
    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-14">
            <h2 class="text-2xl font-extrabold mb-6">أعمال أخرى</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $rel)
                    <a href="{{ route('portfolio.show', $rel->slug) }}"
                       class="group flex flex-col overflow-hidden rounded-2xl border border-line bg-white hover:border-brand hover:shadow-lg transition-all">
                        <div class="aspect-[16/11] overflow-hidden bg-surface">
                            @if ($rel->coverUrl())
                                <img src="{{ $rel->coverUrl() }}" alt="{{ $rel->title }}" width="640" height="440" loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-navy to-[#16343a] flex items-center justify-center text-white/70" aria-hidden="true">
                                    <x-service-icon name="install" class="w-10 h-10" />
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold group-hover:text-brand transition-colors">{{ $rel->title }}</h3>
                            @if ($rel->area_name)<p class="text-muted text-sm mt-1">{{ $rel->area_name }}</p>@endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
