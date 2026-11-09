<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\PhoneNumber;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * تأكيد الطلب + تتبّعه — المرحلة 6C.
 *
 * - التأكيد: متاح للطلب اللي اتعمل دلوقتي بس (عبر الـ session) — مفيش كشف
 *   لبيانات طلب لحد تاني برقم الطلب لوحده.
 * - التتبّع: بالرقم + الموبايل سوا (الاتنين لازم يطابقوا) — من غير كشف بيانات.
 */
class OrderController extends Controller
{
    /** صفحة تأكيد الطلب بعد الإنشاء مباشرة (session-gated). */
    public function confirmation(Request $request): View|RedirectResponse
    {
        $orderId = $request->session()->get('last_order_id');

        if (! $orderId) {
            return redirect()->route('order.track')->with('track_info', 'لمتابعة طلبك اكتب رقم الطلب والموبايل.');
        }

        $order = Order::with('items')->find($orderId);

        if (! $order) {
            return redirect()->route('order.track');
        }

        app(Seo::class)->title('تم استلام طلبك')->canonical('/order/confirmation')->robots('noindex,nofollow');

        return view('shop.order-confirmation', [
            'title' => 'تم استلام طلبك',
            'order' => $order,
        ]);
    }

    /** فورم التتبّع (GET). */
    public function trackForm(): View
    {
        app(Seo::class)->title('تتبّع الطلب')->canonical('/track')->robots('noindex,nofollow');

        return view('shop.track', ['title' => 'تتبّع الطلب', 'order' => null]);
    }

    /** البحث عن الطلب بالرقم + الموبايل (POST). */
    public function track(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:40'],
            'phone'        => ['required', 'string', 'max:20'],
        ], [
            'order_number.required' => 'اكتب رقم الطلب.',
            'phone.required'        => 'اكتب رقم الموبايل اللي طلبت بيه.',
        ]);

        $phone = PhoneNumber::normalize($data['phone']);

        $order = Order::with('items')
            ->where('order_number', trim($data['order_number']))
            ->where('customer_phone', $phone)
            ->first();

        app(Seo::class)->title('تتبّع الطلب')->canonical('/track')->robots('noindex,nofollow');

        if (! $order) {
            return back()
                ->withInput($request->only('order_number'))
                ->with('track_error', 'مفيش طلب بالرقم والموبايل دول. اتأكد من البيانات وحاول تاني.');
        }

        return view('shop.track', ['title' => 'تتبّع الطلب', 'order' => $order]);
    }
}
