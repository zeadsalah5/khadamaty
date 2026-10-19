<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Seo;
use App\Support\Store\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * المفضلة (المرحلة 6D) — تشتغل للضيف (session) والمسجّل (DB). كل الصفحات noindex.
 */
class WishlistController extends Controller
{
    public function index(): View
    {
        app(Seo::class)->title('المفضلة')->canonical('/wishlist')->robots('noindex,nofollow');

        return view('shop.wishlist', [
            'title'       => 'المفضلة',
            'products'    => Wishlist::products(),
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => 'المتجر', 'url' => '/shop'],
                ['label' => 'المفضلة', 'url' => '/wishlist'],
            ],
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);

        $added = Wishlist::toggle((int) $data['product_id']);

        return back()->with('wishlist_success', $added ? 'اتضاف للمفضلة.' : 'اتشال من المفضلة.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);

        Wishlist::remove((int) $data['product_id']);

        return back()->with('wishlist_success', 'تم الحذف من المفضلة.');
    }
}
