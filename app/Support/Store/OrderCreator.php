<?php

namespace App\Support\Store;

use App\Models\Area;
use App\Models\Order;
use App\Models\Product;
use App\Payments\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * إنشاء الطلب بشكل آمن — المرحلة 6C.
 *
 * كله جوّه DB::transaction مع lockForUpdate على صفوف المنتجات:
 * - إعادة التحقق من المخزون تحت القفل => منع بيع آخر قطعة مرتين.
 * - الأسعار بتتقرا من الداتابيز (server-side) — مفيش ثقة في أي سعر من العميل.
 * - خصم المخزون (حجز)، والمستعمل كمية 1.
 * - رقم طلب مقروء وفريد.
 */
class OrderCreator
{
    /**
     * @param  array{name:string, phone:string, address:string, notes?:?string, ip?:?string, customer_id?:?int}  $customer
     *
     * @throws CartException لو السلة فاضية أو منتج نفد مخزونه.
     */
    public static function create(array $customer, Area $area, PaymentMethod $payment): Order
    {
        $rawItems = Cart::raw();

        if (empty($rawItems)) {
            throw new CartException('السلة فاضية.');
        }

        if (! $area->isDeliverable()) {
            throw new CartException('المنطقة دي خارج نطاق التوصيل حالياً.');
        }

        return DB::transaction(function () use ($rawItems, $customer, $area, $payment) {
            // قفل صفوف المنتجات لحد ما الـ transaction تخلص (منع التزامن).
            $products = Product::query()
                ->whereIn('id', array_keys($rawItems))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $itemsPayload = [];
            $reservations = []; // [product_id => qty]

            foreach ($rawItems as $id => $wantedQty) {
                $product = $products->get($id);

                // المنتج لازم يكون منشور + له سعر حقيقي.
                if (! $product || ! $product->is_published || ! $product->hasRealPrice()) {
                    continue; // اتشال من النشر => نتخطّاه بدل ما نكسر الطلب.
                }

                // الكمية المطلوبة بعد القصّ (المستعمل = 1).
                $qty = $product->isUsed() ? 1 : max(1, (int) $wantedQty);

                // إعادة التحقق تحت القفل: فيه مخزون كفاية؟
                if ($product->isSold() || (int) $product->stock_qty < $qty) {
                    throw new CartException('للأسف «' . $product->name_ar . '» نفد من المخزون. عدّل السلة وحاول تاني.');
                }

                $unitPrice = (float) $product->displayPrice();
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $itemsPayload[] = [
                    'product_id' => $product->id,
                    'name'       => $product->name_ar,
                    'sku'        => $product->sku,
                    'condition'  => $product->condition,
                    'unit_price' => $unitPrice,
                    'qty'        => $qty,
                    'line_total' => $lineTotal,
                ];

                $reservations[$product->id] = $qty;
            }

            if (empty($itemsPayload)) {
                throw new CartException('مفيش منتجات متاحة في السلة.');
            }

            $deliveryFee = (float) $area->delivery_fee;
            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'order_number'   => static::generateNumber(),
                'status'         => 'new',
                'customer_id'    => $customer['customer_id'] ?? null,
                'customer_name'  => $customer['name'],
                'customer_phone' => $customer['phone'],
                'address'        => $customer['address'],
                'area_id'        => $area->id,
                'area_name'      => $area->name_ar,
                'notes'          => $customer['notes'] ?? null,
                'subtotal'       => $subtotal,
                'delivery_fee'   => $deliveryFee,
                'total'          => $total,
                'payment_method' => $payment->key(),
                'payment_status' => 'pending',
                'ip'             => $customer['ip'] ?? null,
            ]);

            $order->items()->createMany($itemsPayload);

            // خصم المخزون (حجز) تحت نفس القفل.
            foreach ($reservations as $productId => $qty) {
                $products->get($productId)->reserve($qty);
            }

            // تحضير الدفع (COD: pending).
            $order->payment_status = $payment->prepare($order);
            $order->save();

            Cart::clear();

            return $order;
        });
    }

    /** رقم طلب مقروء وفريد: KH-{ymd}-{4 أرقام}. */
    protected static function generateNumber(): string
    {
        do {
            $number = 'KH-' . now()->format('ymd') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
