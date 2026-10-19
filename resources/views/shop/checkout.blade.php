@extends('layouts.app')

{{--
    صفحة إتمام الطلب /checkout — المرحلة 6C (دفع عند الاستلام).
    $lines, $subtotal, $areas (منشورة + delivers، مع delivery_fee), $payments
    noindex. السعر النهائي بيتحسب server-side — الـ JS للمعاينة بس.
--}}
@section('content')
    <x-breadcrumbs :items="$breadcrumbs" />

    <section class="mx-auto max-w-5xl px-4 pt-6 pb-16 md:pb-20">
        <h1 class="text-2xl font-extrabold md:text-3xl">إتمام الطلب</h1>
        <p class="mt-2 text-muted">الدفع عند الاستلام — تدفع كاش للمندوب لما الطلب يوصلك.</p>

        @if (session('cart_error'))
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('cart_error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-bold">فيه حاجة محتاجة تظبط:</p>
                <ul class="mt-1 list-disc ps-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-3">
            @csrf

            {{-- فخ السبام (honeypot) — لازم يفضل فاضي، مخفي عن المستخدم --}}
            <div aria-hidden="true" class="absolute -left-[9999px] h-0 w-0 overflow-hidden">
                <label for="website">سيبه فاضي</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            {{-- ====== بيانات التوصيل ====== --}}
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-line bg-white p-5 md:p-6">
                    <h2 class="text-lg font-bold">بيانات التوصيل</h2>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="mb-1.5 block text-sm font-semibold">الاسم بالكامل <span class="text-red-600">*</span></label>
                            <input id="name" type="text" name="name" value="{{ old('name', ($customer ?? null)?->name) }}" required autocomplete="name"
                                   class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('name') border-red-400 @enderror">
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-1.5 block text-sm font-semibold">رقم الموبايل <span class="text-red-600">*</span></label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone', ($customer ?? null)?->phone) }}" required
                                   inputmode="numeric" autocomplete="tel" placeholder="01xxxxxxxxx" dir="ltr"
                                   class="w-full rounded-xl border border-line px-4 py-2.5 text-right focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('phone') border-red-400 @enderror">
                            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="area" class="mb-1.5 block text-sm font-semibold">المنطقة <span class="text-red-600">*</span></label>
                            @if ($areas->isEmpty())
                                <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-900">التوصيل غير متاح حالياً. كلّمنا على واتساب لترتيب الطلب.</p>
                            @else
                                <select id="area" name="area" required data-delivery-select
                                        class="w-full rounded-xl border border-line bg-white px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('area') border-red-400 @enderror">
                                    <option value="" selected disabled>اختار المنطقة...</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}" data-fee="{{ (float) $area->delivery_fee }}"
                                                data-note="{{ $area->delivery_note }}" @selected(old('area') == $area->id)>
                                            {{ $area->name_ar }} — {{ (float) $area->delivery_fee > 0 ? number_format($area->delivery_fee) . ' ج.م توصيل' : 'توصيل مجاني' }}
                                        </option>
                                    @endforeach
                                </select>
                                <p data-delivery-note class="mt-1.5 hidden text-xs text-muted"></p>
                            @endif
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="mb-1.5 block text-sm font-semibold">العنوان بالتفصيل <span class="text-red-600">*</span></label>
                            <textarea id="address" name="address" rows="2" required autocomplete="street-address" placeholder="المدينة، الحي، الشارع، رقم العمارة والدور والشقة"
                                      class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                            @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="mb-1.5 block text-sm font-semibold">ملاحظات (اختياري)</label>
                            <textarea id="notes" name="notes" rows="2" placeholder="أي تفاصيل تساعد المندوب يوصلك"
                                      class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ====== طريقة الدفع ====== --}}
                <div class="mt-6 rounded-2xl border border-line bg-white p-5 md:p-6">
                    <h2 class="text-lg font-bold">طريقة الدفع</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($payments as $key => $method)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line p-4 transition-colors hover:border-brand has-[:checked]:border-brand has-[:checked]:bg-brand-50">
                                <input type="radio" name="payment" value="{{ $key }}" class="mt-1 accent-brand"
                                       @checked(old('payment', $loop->first ? $key : null) === $key)>
                                <span>
                                    <span class="font-bold">{{ $method->label() }}</span>
                                    <span class="mt-0.5 block text-sm text-muted">{{ $method->description() }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ====== ملخّص الطلب ====== --}}
            <aside class="lg:col-span-1">
                <div class="sticky top-20 rounded-2xl border border-line bg-surface p-5">
                    <h2 class="text-lg font-bold">ملخّص الطلب</h2>

                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($lines as $line)
                            <li class="flex items-start justify-between gap-2">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold">{{ $line->product->name_ar }}</span>
                                    <span class="text-muted">{{ $line->qty }} × {{ number_format($line->unit_price) }}</span>
                                </span>
                                <span class="shrink-0 font-semibold">{{ number_format($line->line_total) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <dl class="mt-4 space-y-2 border-t border-line pt-4 text-sm" aria-live="polite">
                        <div class="flex items-center justify-between">
                            <dt class="text-muted">إجمالي المنتجات</dt>
                            <dd class="font-semibold">{{ number_format($subtotal) }} ج.م</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted">التوصيل</dt>
                            <dd class="font-semibold" data-delivery-fee data-subtotal="{{ $subtotal }}">—</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-line pt-2 text-base">
                            <dt class="font-bold">الإجمالي</dt>
                            <dd class="font-extrabold text-brand" data-grand-total>—</dd>
                        </div>
                    </dl>

                    <button type="submit" @disabled($areas->isEmpty())
                            class="mt-5 w-full rounded-full bg-accent px-6 py-3 font-bold text-white transition-colors hover:bg-accent-600 disabled:cursor-not-allowed disabled:opacity-50">
                        تأكيد الطلب
                    </button>
                    <p class="mt-3 text-center text-xs text-muted">بتأكيد الطلب أنت موافق على التواصل معاك لتأكيد الميعاد.</p>
                </div>
            </aside>
        </form>
    </section>

    <script>
        (function () {
            const select = document.querySelector('[data-delivery-select]');
            const feeEl = document.querySelector('[data-delivery-fee]');
            const totalEl = document.querySelector('[data-grand-total]');
            const noteEl = document.querySelector('[data-delivery-note]');
            if (!feeEl || !totalEl) return;

            const subtotal = parseFloat(feeEl.dataset.subtotal || '0');
            const fmt = (n) => new Intl.NumberFormat('en-US').format(Math.round(n)) + ' ج.م';

            function update() {
                const opt = select ? select.options[select.selectedIndex] : null;
                const hasFee = opt && opt.value && opt.dataset.fee !== undefined;
                const fee = hasFee ? parseFloat(opt.dataset.fee || '0') : null;

                feeEl.textContent = fee === null ? '—' : (fee > 0 ? fmt(fee) : 'مجاني');
                totalEl.textContent = fmt(subtotal + (fee || 0));

                if (noteEl) {
                    const note = opt ? (opt.dataset.note || '') : '';
                    noteEl.textContent = note;
                    noteEl.classList.toggle('hidden', !note);
                }
            }

            if (select) select.addEventListener('change', update);
            update();
        })();
    </script>
@endsection
