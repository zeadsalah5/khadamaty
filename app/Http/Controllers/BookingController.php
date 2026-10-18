<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request)
    {
        $data = $request->safe()->except('website');
        $data['ip'] = $request->ip();
        $data['devices_count'] = $data['devices_count'] ?? 1;

        Booking::create($data);

        // بنرجّع لنفس الصفحة اللي اتبعت منها الفورم (الرئيسية أو /request-service)
        // ونوقف عند قسم الحجز عشان رسالة النجاح تبان.
        $back = url()->previous();
        $target = str_contains($back, '#') ? $back : $back . '#booking';

        return redirect()
            ->to($target)
            ->with('booking_success', 'تم استلام طلبك بنجاح! هنتواصل معاك في أقرب وقت لتأكيد المعاد.');
    }
}
