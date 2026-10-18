<footer class="bg-navy text-white mt-20">
    <div class="mx-auto max-w-6xl px-4 py-14 grid gap-10 md:grid-cols-4">
        <div class="md:col-span-2">
            <x-logo variant="light" />
            <p class="mt-4 text-sm text-white/70 max-w-sm leading-relaxed">
                {{ config('site.tagline') }}. بنخدم {{ config('site.areas_label') }}
                بفنيين متخصصين ومواعيد مرنة والدفع بعد الشغل.
            </p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-accent hover:bg-accent-600 text-sm font-semibold transition-colors">
                    واتساب
                </a>
                <a href="tel:{{ config('site.phone_tel') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/25 hover:border-white text-sm font-semibold transition-colors">
                    {{ config('site.phone') }}
                </a>
            </div>
        </div>

        <div>
            <h3 class="font-bold mb-4">روابط سريعة</h3>
            <ul class="space-y-2.5 text-sm text-white/70">
                <li><a href="{{ route('services.index') }}" class="hover:text-white">الخدمات</a></li>
                <li><a href="{{ route('areas.index') }}" class="hover:text-white">المناطق</a></li>
                <li><a href="{{ route('brands.index') }}" class="hover:text-white">الماركات</a></li>
                <li><a href="{{ route('prices') }}" class="hover:text-white">الأسعار</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-white">مين إحنا</a></li>
                <li><a href="{{ route('faq') }}" class="hover:text-white">الأسئلة الشائعة</a></li>
                <li><a href="{{ route('diagnose') }}" class="hover:text-white">شخّص عطلك</a></li>
                <li><a href="{{ route('reviews') }}" class="hover:text-white">آراء العملاء</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">اتصل بينا</a></li>
                <li><a href="{{ route('request-service') }}" class="hover:text-white">اطلب خدمة</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-bold mb-4">مناطق الخدمة</h3>
            @php $footerAreas = \App\Models\Area::query()->published()->orderBy('name_ar')->take(6)->get(); @endphp
            <ul class="space-y-2.5 text-sm text-white/70">
                @if ($footerAreas->isNotEmpty())
                    @foreach ($footerAreas as $area)
                        <li><a href="{{ '/areas/' . $area->slug }}" class="hover:text-white">{{ $area->name_ar }}</a></li>
                    @endforeach
                    <li><a href="{{ route('areas.index') }}" class="text-white/90 hover:text-white font-semibold">كل المناطق</a></li>
                @else
                    @foreach (array_slice(config('site.areas'), 0, 6) as $area)
                        <li>{{ $area }}</li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto max-w-6xl px-4 py-5 text-center text-xs text-white/55">
            © {{ date('Y') }} {{ config('site.name') }} — جميع الحقوق محفوظة.
        </div>
    </div>
</footer>
