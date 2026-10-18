@props(['items' => []])
{{--
    items: [['label' => 'الرئيسية', 'url' => '/'], ['label' => 'الخدمات', 'url' => '/services'], ...]
    آخر عنصر هو الصفحة الحالية (aria-current، بدون رابط).
--}}
@if (! empty($items))
    @php $last = count($items) - 1; @endphp
    <nav aria-label="مسار التصفّح" class="mx-auto max-w-6xl px-4 pt-6 text-sm">
        <ol class="flex flex-wrap items-center gap-1.5 text-muted">
            @foreach (array_values($items) as $i => $item)
                <li class="flex items-center gap-1.5">
                    @if ($i === $last)
                        <span class="text-ink font-semibold" aria-current="page">{{ $item['label'] }}</span>
                    @else
                        <a href="{{ $item['url'] }}" class="hover:text-brand transition-colors">{{ $item['label'] }}</a>
                        <svg viewBox="0 0 24 24" class="w-3.5 h-3.5 shrink-0 opacity-60 rotate-180" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    {{-- JSON-LD: الروابط لازم تكون مطلقة --}}
    @php
        $abs = [];
        foreach ($items as $it) {
            $u = $it['url'] ?? null;
            if ($u && ! \Illuminate\Support\Str::startsWith($u, ['http://', 'https://'])) {
                $u = rtrim(app('App\\Support\\Seo')->baseUrl(), '/') . '/' . ltrim($u, '/');
            }
            $abs[] = ['label' => $it['label'] ?? null, 'url' => $u];
        }
    @endphp
    <x-schema.breadcrumb-list :items="$abs" />
@endif
