@props([
    'heading'       => 'احجز فني دلوقتي',
    'sub'           => 'املأ البيانات وهنتواصل معاك نأكد المعاد.',
    'service'       => null,   // اسم خدمة يتحدد مبدئياً في القايمة
    'area'          => null,   // اسم منطقة يتحدد مبدئياً في القايمة
    'brand'         => null,   // الماركة اللي جه منها العميل (صفحة ماركة/ماركة×خدمة)
    'showAppliance' => true,   // إظهار اختيار نوع الجهاز
])

{{--
    فورم الحجز — مكوّن مشترك للرئيسية وصفحة /request-service.
    بيبعت POST لـ booking.store، وبيرجّع لنفس الصفحة مع رسالة نجاح.
    الحماية: honeypot (website) + throttle على الراوت + CSRF.
--}}

@php $svc = old('service', $service); $areaVal = old('area', $area); @endphp

<div class="bg-white rounded-3xl p-6 md:p-10 shadow-2xl">
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-extrabold mb-2">{{ $heading }}</h2>
        <p class="text-muted text-lg">{{ $sub }}</p>
    </div>

    @if (session('booking_success'))
        <div class="mb-6 rounded-xl bg-accent/10 border border-accent/30 text-accent-600 px-5 py-4 text-center font-semibold" role="status">
            {{ session('booking_success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('booking.store') }}" class="grid gap-5 sm:grid-cols-2" novalidate>
        @csrf

        {{-- فخ السبام (مخفي عن المستخدم) --}}
        <div class="hidden" aria-hidden="true">
            <label>لو إنت إنسان سيب الخانة دي فاضية
                <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
            </label>
        </div>

        {{-- حقول مخفية: الماركة (لو الفورم في صفحة ماركة) + رابط الصفحة المصدر --}}
        <input type="hidden" name="brand" value="{{ old('brand', $brand) }}">
        <input type="hidden" name="source_url" value="{{ old('source_url', url()->current()) }}">

        <div>
            <label for="name" class="block text-sm font-semibold mb-1.5">الاسم <span class="text-brand">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                   autocomplete="name"
                   class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-0 @error('name') border-brand @enderror"
                   placeholder="اسمك بالكامل…">
            @error('name') <p class="text-brand text-sm mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-semibold mb-1.5">رقم الموبايل <span class="text-brand">*</span></label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required inputmode="numeric"
                   autocomplete="tel"
                   class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-0 @error('phone') border-brand @enderror"
                   placeholder="مثلاً: 01xxxxxxxxx">
            @error('phone') <p class="text-brand text-sm mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="area" class="block text-sm font-semibold mb-1.5">المنطقة <span class="text-brand">*</span></label>
            <select id="area" name="area" required
                    class="w-full rounded-xl border border-line px-4 py-3 bg-white focus:border-brand focus:ring-0 @error('area') border-brand @enderror">
                <option value="">اختر المنطقة</option>
                @foreach (site('areas') as $areaOption)
                    <option value="{{ $areaOption }}" @selected($areaVal === $areaOption)>{{ $areaOption }}</option>
                @endforeach
            </select>
            @error('area') <p class="text-brand text-sm mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="service" class="block text-sm font-semibold mb-1.5">نوع الخدمة <span class="text-brand">*</span></label>
            <select id="service" name="service" required
                    class="w-full rounded-xl border border-line px-4 py-3 bg-white focus:border-brand focus:ring-0 @error('service') border-brand @enderror">
                <option value="">اختر الخدمة</option>
                @foreach (site('booking_services') as $s)
                    <option value="{{ $s }}" @selected($svc === $s)>{{ $s }}</option>
                @endforeach
            </select>
            @error('service') <p class="text-brand text-sm mt-1.5">{{ $message }}</p> @enderror
        </div>

        @if ($showAppliance)
            <div>
                <label for="appliance" class="block text-sm font-semibold mb-1.5">نوع الجهاز</label>
                <select id="appliance" name="appliance"
                        class="w-full rounded-xl border border-line px-4 py-3 bg-white focus:border-brand focus:ring-0 @error('appliance') border-brand @enderror">
                    <option value="">اختر الجهاز (اختياري)</option>
                    @foreach (site('booking_appliances', []) as $ap)
                        <option value="{{ $ap }}" @selected(old('appliance') === $ap)>{{ $ap }}</option>
                    @endforeach
                </select>
                @error('appliance') <p class="text-brand text-sm mt-1.5">{{ $message }}</p> @enderror
            </div>
        @endif

        <div>
            <label for="devices_count" class="block text-sm font-semibold mb-1.5">عدد الأجهزة</label>
            <input type="number" id="devices_count" name="devices_count" value="{{ old('devices_count', 1) }}" min="1" max="50"
                   class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-0 @error('devices_count') border-brand @enderror">
            @error('devices_count') <p class="text-brand text-sm mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="{{ $showAppliance ? 'sm:col-span-2' : '' }}">
            <label for="preferred_time" class="block text-sm font-semibold mb-1.5">الوقت المناسب</label>
            <input type="text" id="preferred_time" name="preferred_time" value="{{ old('preferred_time') }}"
                   autocomplete="off"
                   class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-0 @error('preferred_time') border-brand @enderror"
                   placeholder="مثلاً: بكرة الصبح، أو بعد العصر…">
            @error('preferred_time') <p class="text-brand text-sm mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="notes" class="block text-sm font-semibold mb-1.5">ملاحظات (اختياري)</label>
            <textarea id="notes" name="notes" rows="3"
                      class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-0 @error('notes') border-brand @enderror"
                      placeholder="أي تفاصيل تحب تقولهالنا…">{{ old('notes') }}</textarea>
            @error('notes') <p class="text-brand text-sm mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <button type="submit"
                    class="w-full py-4 rounded-full bg-brand hover:bg-brand-600 text-white font-extrabold text-lg transition-colors">
                ابعت طلب الحجز
            </button>
            <p class="text-center text-xs text-muted mt-3">بتضغط إرسال معناه إنك موافق نتواصل معاك على الرقم ده.</p>
        </div>
    </form>
</div>
