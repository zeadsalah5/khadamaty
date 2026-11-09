<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Seo;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * «طلباتي» — طلبات العميل المسجّل بحالاتها (المرحلة 6D). كل الصفحات noindex.
 */
class OrdersController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        $orders = $customer->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        app(Seo::class)->title('طلباتي')->canonical('/account/orders')->robots('noindex,nofollow');

        return view('account.orders', [
            'title'       => 'طلباتي',
            'orders'      => $orders,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => 'حسابي', 'url' => '/account/orders'],
                ['label' => 'طلباتي', 'url' => '/account/orders'],
            ],
        ]);
    }

    public function show(Order $order): View
    {
        $customer = Auth::guard('customer')->user();

        // الطلب لازم يخص العميل الحالي — غير كده 404 (مفيش كشف بيانات).
        if ($order->customer_id !== $customer->id) {
            throw new NotFoundHttpException();
        }

        $order->load('items');

        app(Seo::class)->title('تفاصيل الطلب ' . $order->order_number)
            ->canonical('/account/orders/' . $order->id)->robots('noindex,nofollow');

        return view('account.order', [
            'title'       => 'الطلب ' . $order->order_number,
            'order'       => $order,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => 'طلباتي', 'url' => '/account/orders'],
                ['label' => $order->order_number, 'url' => '/account/orders/' . $order->id],
            ],
        ]);
    }
}
