<?php

namespace App\Support;

use App\Mail\NewBookingNotification;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * إشعار صاحب الموقع بحجز خدمة جديد (فورم «احجز فني»).
 * بيتبعت لإيميل التنبيه (order_notify_email) لو متظبط. best-effort:
 * أي خطأ في الميل مايكسرش إنشاء الحجز — بيتسجّل في اللوج بس.
 */
class BookingNotifier
{
    public static function notify(Booking $booking): void
    {
        $email = trim((string) Settings::get('order_notify_email', ''));

        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new NewBookingNotification($booking));
        } catch (\Throwable $e) {
            Log::warning('فشل إرسال إشعار الحجز بالإيميل: ' . $e->getMessage(), ['booking' => $booking->id]);
        }
    }
}
