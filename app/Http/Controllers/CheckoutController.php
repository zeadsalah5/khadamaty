<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Area;
use App\Payments\PaymentManager;
use App\Support\Seo;
use App\Support\Store\Cart;
use App\Support\Store\CartException;
use App\Support\Store\OrderCreator;
use App\Support\Store\OrderNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * الـ checkout بالدفع عند الاستلام — المرحلة 6C.
 * بدون إلزام تسجيل. السعر كله server-side. نوع الصفحة: معاملات (noindex، no-cache).
 */
class CheckoutController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.show')->with('cart_error', 'السلة فاضية. اختار منتجات الأول.');
        }

        app(Seo::class)
            ->title('إتمام الطلب')
            ->canonical('/checkout')
            ->robots('noindex,nofollow');

        // مناطق التوصيل المتاحة (منشورة + delivers) — مع الرسوم لحساب الإجمالي.
        $areas = Area::query()->published()
            ->where('delivers', true)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'delivery_fee', 'delivery_note']);

        return view('shop.checkout', [
            'title'       => 'إتمام الطلب',
            'lines'       => $lines,
            'subtotal'    => Cart::subtotal(),
            'areas'       => $areas,
            'payments'    => app(PaymentManager::class)->available(),
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => 'السلة', 'url' => '/cart'],
                ['label' => 'إتمام الطلب', 'url' => '/checkout'],
            ],
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $manager = app(PaymentManager::class);
        $payment = $manager->get($request->input('payment')) ?? $manager->default();

        if (! $payment) {
            return back()->withInput()->with('cart_error', 'طريقة الدفع غير متاحة.');
        }

        $area = Area::query()->published()->where('delivers', true)->findOrFail($request->integer('area'));

        try {
            $order = OrderCreator::create([
                'name'    => $request->string('name')->trim()->value(),
                'phone'   => $request->string('phone')->value(),
                'address' => $request->string('address')->trim()->value(),
                'notes'   => $request->string('notes')->trim()->value() ?: null,
                'ip'      => $request->ip(),
            ], $area, $payment);
        } catch (CartException $e) {
            return redirect()->route('cart.show')->with('cart_error', $e->getMessage());
        }

        // إشعار صاحب الموقع (إيميل best-effort؛ الواتساب في لوحة الإدارة).
        OrderNotifier::notify($order);

        // صلاحية عرض صفحة التأكيد: الطلب اللي اتعمل دلوقتي بس (عبر الـ session).
        session(['last_order_id' => $order->id]);

        return redirect()->route('order.confirmation');
    }
}
