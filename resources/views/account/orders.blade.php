@extends('layouts.app')

{{-- طلباتي (المرحلة 6D) — طلبات العميل المسجّل بحالاتها. noindex. --}}
@section('content')
    <section class="mx-auto max-w-4xl px-4 pt-8 pb-16 md:pb-20">
        <x-breadcrumbs :items="$breadcrumbs" />

        @include('account.partials.header', ['active' => 'orders'])

        @if (session('account_success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('account_success') }}</div>
        @endif

        @if ($orders->isEmpty())
            <div class="rounded-2xl border border-dashed border-line bg-surface p-10 text-center">
                <p class="text-muted">لسه مفيش طلبات على حسابك.</p>
                <a href="{{ route('shop.index') }}" class="mt-4 inline-flex rounded-full bg-brand px-6 py-2.5 font-bold text-white transition-colors hover:bg-brand-600">ابدأ التسوّق</a>
            </div>
        @else
            <ul class="space-y-3">
                @foreach ($orders as $order)
                    @php $cancelled = in_array($order->status, ['cancelled', 'returned'], true); @endphp
                    <li>
                        <a href="{{ route('account.orders.show', $order) }}"
                           class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white p-4 transition-colors hover:border-brand">
                            <div>
                                <p class="font-extrabold tracking-wide" dir="ltr">{{ $order->order_number }}</p>
                                <p class="mt-0.5 text-sm text-muted">{{ $order->created_at->translatedFormat('j F Y') }} · {{ $order->items_count }} منتج</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-brand">{{ number_format((float) $order->total) }} ج.م</span>
                                <span class="rounded-full px-3 py-1 text-xs font-bold
                                    {{ $cancelled ? 'bg-red-100 text-red-700' : ($order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-800') }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
