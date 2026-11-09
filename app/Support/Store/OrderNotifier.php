<?php

namespace App\Support\Store;

use App\Mail\NewOrderNotification;
use App\Models\Order;
use App\Support\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * إشعار صاحب الموقع بطلب جديد — المرحلة 6C.
 *
 * قناتين (بتتظبطا من الداشبورد):
 *  - إيميل: يتبعت تلقائياً لو order_notify_email متظبط (best-effort — أي خطأ
 *    في الميل مايكسرش إنشاء الطلب، بيتسجّل في اللوج بس).
 *  - واتساب: رابط wa.me جاهز بالنص بيظهر في لوحة الطلبات (واتساب مبيتبعتش
 *    تلقائياً — صاحب الموقع بيضغط الرابط).
 */
class OrderNotifier
{
    /** إرسال إشعار الإيميل لصاحب الموقع (لو متظبط). آمن ومايرميش exception. */
    public static function notify(Order $order): void
    {
        $email = trim((string) Settings::get('order_notify_email', ''));

        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new NewOrderNotification($order));
        } catch (\Throwable $e) {
            Log::warning('فشل إرسال إشعار الطلب بالإيميل: ' . $e->getMessage(), ['order' => $order->order_number]);
        }
    }

    /** رقم واتساب صاحب الموقع للإشعارات (order_notify_whatsapp وإلا whatsapp العام). */
    public static function ownerWhatsapp(): string
    {
        $number = trim((string) Settings::get('order_notify_whatsapp', ''));

        return $number !== '' ? $number : trim((string) Settings::get('whatsapp', ''));
    }

    /** رابط wa.me جاهز بنص الطلب لصاحب الموقع (يظهر في لوحة الإدارة). */
    public static function ownerWhatsappLink(Order $order): ?string
    {
        $number = static::ownerWhatsapp();

        if ($number === '') {
            return null;
        }

        $lines = [
            'طلب جديد #' . $order->order_number,
            'العميل: ' . $order->customer_name,
            'الموبايل: ' . $order->customer_phone,
            'المنطقة: ' . $order->area_name,
            'العنوان: ' . $order->address,
            'الإجمالي: ' . number_format((float) $order->total) . ' ج.م (دفع عند الاستلام)',
        ];

        return 'https://wa.me/' . $number . '?text=' . rawurlencode(implode("\n", $lines));
    }
}
