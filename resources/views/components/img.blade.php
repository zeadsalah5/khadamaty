@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'lazy' => true,       // false للصور فوق الطيّة (hero)
])
{{--
    مكوّن صورة بمعايير الأداء:
    - width/height صريحة علشان نمنع layout shift (CLS).
    - loading="lazy" + decoding="async" افتراضياً.
    - فضّل مسارات WebP بأبعاد محددة. مثال:
      <x-img src="images/technicians/tech-1.webp" alt="فني تكييفات" :width="800" :height="600" />
--}}
<img
    src="{{ \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '/']) ? $src : asset($src) }}"
    alt="{{ $alt }}"
    @if ($width) width="{{ $width }}" @endif
    @if ($height) height="{{ $height }}" @endif
    loading="{{ $lazy ? 'lazy' : 'eager' }}"
    decoding="async"
    {{ $attributes }}
>
