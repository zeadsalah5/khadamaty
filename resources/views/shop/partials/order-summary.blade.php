{{--
    ملخّص طلب قابل لإعادة الاستخدام (تأكيد + تتبّع) — المرحلة 6C.
    $order (محمّل معاه items)
--}}
@php
    $statuses = \App\Models\Order::STATUSES;
    $steps = ['new', 'confirmed', 'out_for_delivery', 'delivered'];
    $cancelled = in_array($order->status, ['cancelled', 'returned'], true);
    $currentIndex = array_search($order->status, $steps, true);
@endphp

<div class="rounded-2xl border border-line bg-white p-5 md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-muted">رقم الطلب</p>
            <p class="text-lg font-extrabold tracking-wide" dir="ltr">{{ $order->order_number }}</p>
        </div>
        <span class="rounded-full px-4 py-1.5 text-sm font-bold
            {{ $cancelled ? 'bg-red-100 text-red-700' : ($order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-800') }}">
            {{ $order->statusLabel() }}
        </span>
    </div>

    {{-- شريط مراحل الطلب (يختفي لو ملغي/مرتجع) --}}
    @unless ($cancelled)
        <ol class="mt-5 grid grid-cols-4 gap-2 text-center text-xs">
            @foreach ($steps as $i => $step)
                @php $done = $currentIndex !== false && $i <= $currentIndex; @endphp
                <li class="flex flex-col items-center gap-1.5">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $done ? 'bg-brand text-white' : 'bg-surface text-muted' }}">{{ $i + 1 }}</span>
                    <span class="{{ $done ? 'font-semibold text-ink' : 'text-muted' }}">{{ $statuses[$step] }}</span>
                </li>
            @endforeach
        </ol>
    @endunless

    {{-- البنود --}}
    <ul class="mt-6 space-y-3 border-t border-line pt-4 text-sm">
        @foreach ($order->items as $item)
            <li class="flex items-start justify-between gap-2">
                <span class="min-w-0">
                    <span class="block font-semibold">{{ $item->name }}</span>
                    <span class="text-muted">{{ $item->qty }} × {{ number_format((float) $item->unit_price) }} ج.م · {{ $item->condition === 'used' ? 'مستعمل' : 'جديد' }}</span>
                </span>
                <span class="shrink-0 font-semibold">{{ number_format((float) $item->line_total) }} ج.م</span>
            </li>
        @endforeach
    </ul>

    <dl class="mt-4 space-y-2 border-t border-line pt-4 text-sm">
        <div class="flex items-center justify-between">
            <dt class="text-muted">إجمالي المنتجات</dt>
            <dd class="font-semibold">{{ number_format((float) $order->subtotal) }} ج.م</dd>
        </div>
        <div class="flex items-center justify-between">
            <dt class="text-muted">التوصيل ({{ $order->area_name }})</dt>
            <dd class="font-semibold">{{ (float) $order->delivery_fee > 0 ? number_format((float) $order->delivery_fee) . ' ج.م' : 'مجاني' }}</dd>
        </div>
        <div class="flex items-center justify-between border-t border-line pt-2 text-base">
            <dt class="font-bold">الإجمالي</dt>
            <dd class="font-extrabold text-brand">{{ number_format((float) $order->total) }} ج.م</dd>
        </div>
    </dl>

    <p class="mt-4 flex items-center gap-1.5 text-xs text-muted">
        <svg viewBox="0 0 24 24" class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4"/><path d="M12 3 4 6v6c0 5 3.5 7.5 8 9 4.5-1.5 8-4 8-9V6z"/></svg>
        الدفع عند الاستلام
    </p>
</div>
