{{--
    رأس صفحات الحساب: اسم العميل + تبويبات + خروج (المرحلة 6D).
    $active = 'orders' | 'wishlist'
--}}
@php $customer = auth('customer')->user(); @endphp

<div class="mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold md:text-3xl">حسابي</h1>
            <p class="mt-1 text-muted">أهلاً {{ $customer->name }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-line px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-brand hover:text-brand">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/></svg>
                خروج
            </button>
        </form>
    </div>

    <nav class="mt-5 flex gap-2 border-b border-line text-sm font-semibold" aria-label="أقسام الحساب">
        <a href="{{ route('account.orders') }}"
           class="-mb-px border-b-2 px-4 py-2.5 transition-colors {{ ($active ?? '') === 'orders' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink' }}">طلباتي</a>
        <a href="{{ route('wishlist.show') }}"
           class="-mb-px border-b-2 px-4 py-2.5 transition-colors {{ ($active ?? '') === 'wishlist' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink' }}">المفضلة</a>
    </nav>
</div>
