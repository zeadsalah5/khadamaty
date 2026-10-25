@extends('layouts.app')

{{--
    صفحة الضمان (/warranty) — مسودة noindex دايماً.
    المحتوى من إعدادات الموقع (warranty_months / warranty_scope / warranty_exclusions).
    بدون schema للضمان. لو الحقول فاضية بتظهر ملاحظة إنها لسه مش متكتبة.
--}}

@section('content')

    <x-breadcrumbs :items="$breadcrumbs" />

    <header class="mx-auto max-w-4xl px-4 pt-6 pb-8">
        <h1 class="text-3xl md:text-4xl font-extrabold mb-3">الضمان على الخدمة</h1>
        <p class="text-muted text-lg leading-relaxed">بنقدّم ضمان على الشغل اللي بننفّذه. التفاصيل تحت.</p>
    </header>

    <section class="mx-auto max-w-4xl px-4 pb-16">
        @php
            $scope = site('warranty_scope');
            $exclusions = site('warranty_exclusions');
            $months = (int) site('warranty_months', 6);
        @endphp

        @if (filled($scope) && filled($exclusions))
            <div class="rounded-2xl border border-accent/30 bg-accent/5 p-6 md:p-8 space-y-5">
                <div class="inline-flex items-center gap-2 rounded-full bg-accent/10 text-accent-600 border border-accent/30 px-4 py-1.5 font-bold">
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    ضمان {{ $months }} شهور
                </div>
                <div>
                    <h2 class="text-xl font-extrabold mb-2">نطاق الضمان</h2>
                    <p class="text-ink leading-loose">{{ $scope }}</p>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold mb-2">الاستثناءات</h2>
                    <p class="text-muted leading-loose">{{ $exclusions }}</p>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface p-6 text-muted leading-relaxed">
                تفاصيل الضمان لسه بتتجهّز. تواصل معانا على {{ site('phone') }} ونوضّحلك تفاصيل الضمان على خدمتك.
            </div>
        @endif
    </section>

@endsection
