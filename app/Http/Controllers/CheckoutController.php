<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Area;
use App\Models\Order;
use App\Payments\PaymentException;
use App\Payments\PaymentManager;
use App\Support\Seo;
use App\Support\Store\Cart;
use App\Support\Store\CartException;
use App\Support\Store\OrderCreator;
use App\Support\Store\OrderNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
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
            'customer'    => auth('customer')->user(),
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
                'name'        => $request->string('name')->trim()->value(),
                'phone'       => $request->string('phone')->value(),
                'address'     => $request->string('address')->trim()->value(),
                'notes'       => $request->string('notes')->trim()->value() ?: null,
                'ip'          => $request->ip(),
                // ربط الطلب بالحساب لو العميل داخل (الضيف بيفضل null).
                'customer_id' => auth('customer')->id(),
            ], $area, $payment);
        } catch (CartException $e) {
            return redirect()->route('cart.show')->with('cart_error', $e->getMessage());
        }

        // صلاحية عرض صفحة التأكيد/الرجوع: الطلب اللي اتعمل دلوقتي بس (عبر الـ session).
        session(['last_order_id' => $order->id]);

        // الدفع الأونلاين: تحويل لبوابة الدفع. (الإشعار بيتبعت لما الدفع يتأكّد من الـ webhook.)
        if ($payment->isOnline()) {
            try {
                $url = $payment->begin($order);
            } catch (PaymentException $e) {
                Log::warning('Online payment could not start', ['order' => $order->order_number, 'error' => $e->getMessage()]);

                return redirect()->route('order.confirmation')->with('payment_error', $e->getMessage());
            }

            if ($url) {
                return redirect()->away($url);
            }
        }

        // الدفع عند الاستلام: إشعار صاحب الموقع فوراً (إيميل best-effort؛ الواتساب في اللوحة).
        OrderNotifier::notify($order);

        return redirect()->route('order.confirmation');
    }

    /**
     * إعادة محاولة الدفع الأونلاين لطلب مستنّي/فشل — للطلب اللي في السيشن بس،
     * وقبل انتهاء المهلة. بيعمل جلسة دفع جديدة ويحوّل للبوابة.
     */
    public function retry(Request $request, Order $order): RedirectResponse
    {
        // نفس صلاحية صفحة التأكيد: الطلب اللي اتعمل في الجلسة دي بس.
        if ((int) $request->session()->get('last_order_id') !== $order->id) {
            return redirect()->route('order.track');
        }

        $retryable = in_array($order->payment_status, ['pending_payment', 'failed'], true)
            && ! ($order->payment_due_at && $order->payment_due_at->isPast());

        if (! $retryable) {
            return redirect()->route('order.confirmation');
        }

        $method = app(PaymentManager::class)->get($order->payment_method);

        if (! $method || ! $method->isOnline()) {
            return redirect()->route('order.confirmation');
        }

        try {
            $url = $method->begin($order);
        } catch (PaymentException $e) {
            return redirect()->route('order.confirmation')->with('payment_error', $e->getMessage());
        }

        return $url ? redirect()->away($url) : redirect()->route('order.confirmation');
    }
}
