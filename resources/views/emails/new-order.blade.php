<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>طلب جديد #{{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:24px;background:#f3f4f6;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#1B2230;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
        <div style="background:#1B2230;color:#ffffff;padding:20px 24px;">
            <h1 style="margin:0;font-size:18px;">طلب جديد #{{ $order->order_number }}</h1>
            <p style="margin:6px 0 0;font-size:13px;color:#cbd5e1;">{{ site('name') }} — الدفع عند الاستلام</p>
        </div>

        <div style="padding:24px;">
            <table role="presentation" style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr><td style="padding:4px 0;color:#6b7280;">العميل</td><td style="padding:4px 0;font-weight:bold;">{{ $order->customer_name }}</td></tr>
                <tr><td style="padding:4px 0;color:#6b7280;">الموبايل</td><td style="padding:4px 0;font-weight:bold;" dir="ltr">{{ $order->customer_phone }}</td></tr>
                <tr><td style="padding:4px 0;color:#6b7280;">المنطقة</td><td style="padding:4px 0;">{{ $order->area_name }}</td></tr>
                <tr><td style="padding:4px 0;color:#6b7280;vertical-align:top;">العنوان</td><td style="padding:4px 0;">{{ $order->address }}</td></tr>
                @if ($order->notes)
                    <tr><td style="padding:4px 0;color:#6b7280;vertical-align:top;">ملاحظات</td><td style="padding:4px 0;">{{ $order->notes }}</td></tr>
                @endif
            </table>

            <h2 style="font-size:15px;margin:20px 0 8px;border-top:1px solid #e5e7eb;padding-top:16px;">المنتجات</h2>
            <table role="presentation" style="width:100%;border-collapse:collapse;font-size:14px;">
                @foreach ($order->items as $item)
                    <tr>
                        <td style="padding:6px 0;">{{ $item->name }} <span style="color:#6b7280;">({{ $item->qty }} × {{ number_format((float) $item->unit_price) }})</span></td>
                        <td style="padding:6px 0;text-align:left;font-weight:bold;">{{ number_format((float) $item->line_total) }} ج.م</td>
                    </tr>
                @endforeach
            </table>

            <table role="presentation" style="width:100%;border-collapse:collapse;font-size:14px;border-top:1px solid #e5e7eb;margin-top:12px;">
                <tr><td style="padding:4px 0;color:#6b7280;">إجمالي المنتجات</td><td style="padding:4px 0;text-align:left;">{{ number_format((float) $order->subtotal) }} ج.م</td></tr>
                <tr><td style="padding:4px 0;color:#6b7280;">التوصيل</td><td style="padding:4px 0;text-align:left;">{{ (float) $order->delivery_fee > 0 ? number_format((float) $order->delivery_fee) . ' ج.م' : 'مجاني' }}</td></tr>
                <tr><td style="padding:8px 0 0;font-weight:bold;font-size:16px;">الإجمالي</td><td style="padding:8px 0 0;text-align:left;font-weight:bold;font-size:16px;color:#E5171F;">{{ number_format((float) $order->total) }} ج.م</td></tr>
            </table>
        </div>
    </div>
    <p style="max-width:560px;margin:16px auto 0;font-size:12px;color:#9ca3af;text-align:center;">إشعار آلي من لوحة {{ site('name') }}.</p>
</body>
</html>
