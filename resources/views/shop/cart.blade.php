@extends('layouts.app')

{{--
    صفحة السلة /cart — المرحلة 6C.
    $lines (Collection من بنود حيّة: product, qty, unit_price, line_total, adjusted), $subtotal
    noindex. كل الأسعار من الداتابيز.
--}}
@section('content')
    <x-breadcrumbs :items="$breadcrumbs" />

    <section class="mx-auto max-w-5xl px-4 pt-6 pb-16 md:pb-20">
        <h1 class="text-2xl font-extrabold md:text-3xl">سلة المشتريات</h1>

        @if (session('cart_success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('cart_success') }}</div>
        @endif
        @if (session('cart_error'))
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('cart_error') }}</div>
        @endif

        @if ($lines->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-line bg-surface px-4 py-16 text-center">
                <p class="text-lg text-muted">سلتك فاضية.</p>
                <a href="{{ route('shop.index') }}"
                   class="mt-5 inline-block rounded-full bg-brand px-6 py-3 font-bold text-white transition-colors hover:bg-brand-600">تصفّح المتجر</a>
            </div>
        @else
            @if ($lines->contains('adjusted', true))
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                    عدّلنا كميات بعض المنتجات حسب المتاح في المخزون.
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- ====== قائمة البنود ====== --}}
                <div class="lg:col-span-2">
                    <ul class="space-y-4">
                        @foreach ($lines as $line)
                            @php $p = $line->product; @endphp
                            <li class="flex gap-4 rounded-2xl border border-line bg-white p-4">
                                <a href="{{ url($p->path()) }}" class="shrink-0">
                                    @if ($p->imageUrl())
                                        <img src="{{ $p->imageUrl() }}" alt="{{ $p->name_ar }}" width="96" height="96"
                                             loading="lazy" decoding="async" class="h-24 w-24 rounded-xl border border-line object-contain">
                                    @else
                                        <span class="flex h-24 w-24 items-center justify-center rounded-xl border border-dashed border-line bg-surface text-xs text-muted">لا صورة</span>
                                    @endif
                                </a>

                                <div class="flex min-w-0 flex-1 flex-col">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <a href="{{ url($p->path()) }}" class="block break-words font-bold leading-snug hover:text-brand">{{ $p->name_ar }}</a>
                                            <p class="mt-0.5 text-sm text-muted">
                                                <span class="{{ $p->isUsed() ? 'text-amber-700' : 'text-sky-700' }}">{{ $p->isUsed() ? 'مستعمل' : 'جديد' }}</span>
                                                · {{ number_format($line->unit_price) }} ج.م
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('cart.remove') }}">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $p->id }}">
                                            <button type="submit" class="text-sm text-muted transition-colors hover:text-red-600" aria-label="حذف {{ $p->name_ar }} من السلة">حذف</button>
                                        </form>
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                                        {{-- الكمية --}}
                                        @if ($p->isUsed())
                                            <span class="text-sm text-muted">الكمية: 1 (قطعة واحدة)</span>
                                        @else
                                            <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $p->id }}">
                                                <label for="qty-{{ $p->id }}" class="text-sm text-muted">الكمية</label>
                                                <input id="qty-{{ $p->id }}" type="number" name="qty" value="{{ $line->qty }}"
                                                       min="1" max="{{ min(20, (int) $p->stock_qty) }}" inputmode="numeric"
                                                       class="w-16 rounded-lg border border-line px-2 py-1.5 text-center focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">
                                                <button type="submit" class="rounded-lg border border-line px-3 py-1.5 text-sm font-semibold transition-colors hover:border-brand hover:text-brand">تحديث</button>
                                            </form>
                                        @endif

                                        <span class="font-extrabold text-brand">{{ number_format($line->line_total) }} ج.م</span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- ====== الملخّص ====== --}}
                <aside class="lg:col-span-1">
                    <div class="sticky top-20 rounded-2xl border border-line bg-surface p-5">
                        <h2 class="text-lg font-bold">ملخّص الطلب</h2>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-muted">إجمالي المنتجات ({{ $lines->sum('qty') }})</dt>
                                <dd class="font-semibold">{{ number_format($subtotal) }} ج.م</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-muted">التوصيل</dt>
                                <dd class="text-muted">بيتحسب حسب المنطقة</dd>
                            </div>
                        </dl>
                        <a href="{{ route('checkout.show') }}"
                           class="mt-5 block rounded-full bg-accent px-6 py-3 text-center font-bold text-white transition-colors hover:bg-accent-600">إتمام الطلب</a>
                        <a href="{{ route('shop.index') }}" class="mt-3 block text-center text-sm font-semibold text-muted hover:text-brand">مواصلة التسوّق</a>
                        <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-muted">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4"/><path d="M12 3 4 6v6c0 5 3.5 7.5 8 9 4.5-1.5 8-4 8-9V6z"/></svg>
                            الدفع عند الاستلام — تدفع لما يوصلك
                        </p>
                    </div>
                </aside>
            </div>
        @endif
    </section>
@endsection
