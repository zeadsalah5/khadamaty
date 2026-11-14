<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>حجز خدمة جديد</title>
</head>
<body style="margin:0;padding:24px;background:#f3f4f6;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#1B2230;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
        <div style="background:#1B2230;color:#ffffff;padding:20px 24px;">
            <h1 style="margin:0;font-size:18px;">حجز خدمة جديد 🛠️</h1>
            <p style="margin:6px 0 0;font-size:13px;color:#cbd5e1;">{{ site('name') }} — طلب من فورم «احجز فني»</p>
        </div>

        <div style="padding:24px;">
            <table role="presentation" style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr><td style="padding:5px 0;color:#6b7280;width:120px;">العميل</td><td style="padding:5px 0;font-weight:bold;">{{ $booking->name }}</td></tr>
                <tr><td style="padding:5px 0;color:#6b7280;">الموبايل</td><td style="padding:5px 0;font-weight:bold;" dir="ltr">
                    <a href="tel:{{ $booking->phone }}" style="color:#1B2230;text-decoration:none;">{{ $booking->phone }}</a>
                </td></tr>
                @if ($booking->service)
                    <tr><td style="padding:5px 0;color:#6b7280;">الخدمة</td><td style="padding:5px 0;">{{ $booking->service }}</td></tr>
                @endif
                @if ($booking->area)
                    <tr><td style="padding:5px 0;color:#6b7280;">المنطقة</td><td style="padding:5px 0;">{{ $booking->area }}</td></tr>
                @endif
                @if ($booking->brand)
                    <tr><td style="padding:5px 0;color:#6b7280;">الماركة</td><td style="padding:5px 0;">{{ $booking->brand }}</td></tr>
                @endif
                @if ($booking->appliance)
                    <tr><td style="padding:5px 0;color:#6b7280;">الجهاز</td><td style="padding:5px 0;">{{ $booking->appliance }}</td></tr>
                @endif
                @if ($booking->devices_count)
                    <tr><td style="padding:5px 0;color:#6b7280;">عدد الأجهزة</td><td style="padding:5px 0;">{{ $booking->devices_count }}</td></tr>
                @endif
                @if ($booking->preferred_time)
                    <tr><td style="padding:5px 0;color:#6b7280;">الوقت المناسب</td><td style="padding:5px 0;">{{ $booking->preferred_time }}</td></tr>
                @endif
                @if ($booking->notes)
                    <tr><td style="padding:5px 0;color:#6b7280;vertical-align:top;">ملاحظات</td><td style="padding:5px 0;">{{ $booking->notes }}</td></tr>
                @endif
            </table>

            @php
                $phoneDigits = preg_replace('/[^0-9]/', '', (string) $booking->phone);
            @endphp
            <div style="margin-top:20px;border-top:1px solid #e5e7eb;padding-top:16px;">
                <a href="tel:{{ $booking->phone }}" style="display:inline-block;background:#E5171F;color:#fff;text-decoration:none;padding:10px 18px;border-radius:999px;font-weight:bold;font-size:14px;margin-left:8px;">اتصل بالعميل</a>
                @if ($phoneDigits !== '')
                    <a href="https://wa.me/2{{ ltrim($phoneDigits, '0') }}" style="display:inline-block;background:#0E9F6E;color:#fff;text-decoration:none;padding:10px 18px;border-radius:999px;font-weight:bold;font-size:14px;">واتساب العميل</a>
                @endif
            </div>
        </div>
    </div>
    <p style="max-width:560px;margin:16px auto 0;font-size:12px;color:#9ca3af;text-align:center;">إشعار آلي من لوحة {{ site('name') }}.</p>
</body>
</html>
