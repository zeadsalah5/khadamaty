@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<x-breadcrumbs :items="$breadcrumbs" />

<header class="mx-auto max-w-6xl px-4 pt-6 pb-10 md:pb-12">
    <h1 class="text-3xl md:text-4xl font-extrabold mb-3">{{ $title }}</h1>
    @if ($subtitle)
        <p class="text-muted text-lg max-w-2xl">{{ $subtitle }}</p>
    @endif
</header>
