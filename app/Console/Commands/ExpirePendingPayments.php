<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

/**
 * إلغاء الطلبات الأونلاين اللي فات ميعاد دفعها وإرجاع مخزونها — المرحلة 6E.
 *
 * بيشتغل مجدول (كل 5 دقايق). بياخد الطلبات pending_payment/failed اللي
 * payment_due_at بتاعها عدّى، ويحوّلها cancelled (اللي بيرجّع المخزون عبر
 * Order::changeStatus) ويعلّم الدفع failed.
 */
class ExpirePendingPayments extends Command
{
    protected $signature = 'payments:expire-pending';

    protected $description = 'إلغاء الطلبات الأونلاين غير المدفوعة بعد انتهاء المهلة وإرجاع المخزون';

    public function handle(): int
    {
        $orders = Order::query()
            ->whereIn('payment_status', ['pending_payment', 'failed'])
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->whereNotIn('status', Order::STOCK_RETURNING) // اتلغى قبل كده؟ نتخطّاه
            ->get();

        foreach ($orders as $order) {
            $order->payment_status = 'failed';
            $order->payment_due_at = null;
            $order->save();

            // بيرجّع المخزون المحجوز (داخل transaction) ويحفظ الحالة cancelled.
            $order->changeStatus('cancelled');

            $this->info("ألغيت الطلب {$order->order_number} (انتهت مهلة الدفع).");
        }

        $this->info("تم فحص {$orders->count()} طلب غير مدفوع.");

        return self::SUCCESS;
    }
}
