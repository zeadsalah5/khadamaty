<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * تقييم منتج (المرحلة 6D).
 *
 * شرط التقييم: العميل مسجّل، وعنده بند طلب «متسلّم» (delivered) للمنتج ده،
 * ومقيّمش المنتج قبل كده. كل تقييم بيبدأ pending لحد المراجعة.
 */
class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title'  => ['nullable', 'string', 'max:120'],
            'body'   => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'rating.required' => 'اختار تقييمك بالنجوم.',
            'rating.between'  => 'التقييم من 1 لـ 5 نجوم.',
            'body.required'   => 'اكتب رأيك في المنتج.',
            'body.min'        => 'الرأي قصير أوي، اكتب تفاصيل أكتر.',
        ]);

        // إثبات الشراء: لازم بند طلب متسلّم للمنتج ده.
        $orderItem = $customer->deliveredOrderItemFor($product->id);
        if (! $orderItem) {
            throw ValidationException::withMessages([
                'body' => 'تقدر تقيّم المنتجات اللي اشتريتها واستلمتها بس.',
            ]);
        }

        // تقييم واحد لكل عميل لكل منتج — التعديل بيرجّعه pending تاني.
        ProductReview::updateOrCreate(
            ['customer_id' => $customer->id, 'product_id' => $product->id],
            [
                'order_item_id' => $orderItem->id,
                'rating'        => (int) $data['rating'],
                'title'         => $data['title'] ?? null,
                'body'          => $data['body'],
                'status'        => 'pending',
            ],
        );

        return redirect($product->path())
            ->with('review_success', 'شكراً لتقييمك! هيظهر بعد المراجعة.');
    }
}
