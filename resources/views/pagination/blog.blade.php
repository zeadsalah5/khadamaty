@if ($paginator->hasPages())
    <nav role="navigation" aria-label="ترقيم الصفحات" class="flex items-center justify-center">
        <ul class="flex flex-wrap items-center gap-1.5">
            {{-- السابق --}}
            @if ($paginator->onFirstPage())
                <li aria-disabled="true">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-line text-muted/50 cursor-default" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="الصفحة السابقة"
                       class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-line hover:border-brand hover:text-brand transition-colors">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                </li>
            @endif

            {{-- أرقام الصفحات --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li aria-disabled="true"><span class="inline-flex items-center justify-center w-10 h-10 text-muted">…</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page">
                                <span class="inline-flex items-center justify-center min-w-10 h-10 px-3 rounded-full bg-brand text-white font-bold tabular-nums">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}"
                                   class="inline-flex items-center justify-center min-w-10 h-10 px-3 rounded-full border border-line font-semibold tabular-nums hover:border-brand hover:text-brand transition-colors">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- التالي --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="الصفحة التالية"
                       class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-line hover:border-brand hover:text-brand transition-colors">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                </li>
            @else
                <li aria-disabled="true">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-line text-muted/50 cursor-default" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
