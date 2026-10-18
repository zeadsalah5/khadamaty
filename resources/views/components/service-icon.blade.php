@props(['name'])

{{-- أيقونة مميزة لكل خدمة. اللون بياخد currentColor --}}
<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
     {{ $attributes->merge(['class' => 'w-7 h-7']) }}
     stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true">
    @switch($name)
        @case('maintenance') {{-- مفتاح ربط / صيانة --}}
            <path d="M14.5 6.5a3.5 3.5 0 0 0-4.8 4.3L4 16.5 7.5 20l5.7-5.7a3.5 3.5 0 0 0 4.3-4.8l-2.3 2.3-2-2 2.3-2.3Z"/>
            @break
        @case('cleaning') {{-- قطرة/رغوة تنظيف --}}
            <path d="M12 3s5 5.5 5 9a5 5 0 0 1-10 0c0-3.5 5-9 5-9Z"/>
            <path d="M9.5 13a2.5 2.5 0 0 0 2.5 2.5"/>
            @break
        @case('freon') {{-- أسطوانة غاز --}}
            <rect x="8" y="7" width="8" height="13" rx="2"/>
            <path d="M10 7V5a2 2 0 0 1 4 0v2M9 11h6"/>
            @break
        @case('install') {{-- وحدة تكييف --}}
            <rect x="3" y="6" width="18" height="8" rx="2"/>
            <path d="M6 10h7M8 18c0 1.5 1 1.5 1 3M15 18c0 1.5 1 1.5 1 3"/>
            @break
        @case('move') {{-- عربة نقل --}}
            <path d="M4 4h2l1 11h11M7 15h12l1-7H6.2"/>
            <circle cx="9" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/>
            @break
        @case('repair') {{-- مفك/إصلاح أعطال --}}
            <path d="M3 21l7-7M14 4a4 4 0 0 1 5 5l-9 9-4 1 1-4 7-7a1.5 1.5 0 0 0-2-2l-1 1"/>
            @break
        @case('contract') {{-- عقد/مستند --}}
            <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/>
            <path d="M14 3v5h5M8.5 13l2 2 4-4"/>
            @break
        @case('pipes') {{-- مواسير --}}
            <path d="M4 8h8a2 2 0 0 1 2 2v4a2 2 0 0 0 2 2h4M4 6v4M20 14v4"/>
            @break
        @case('diagnose') {{-- كشف وتشخيص --}}
            <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3M8.5 11h5M11 8.5v5"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
    @endswitch
</svg>
