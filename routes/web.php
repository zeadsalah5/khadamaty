<?php

use App\Http\Controllers\Account\OrdersController as AccountOrdersController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\Shop\CompareController;
use App\Http\Controllers\Shop\ProductReviewController;
use App\Http\Controllers\Shop\WishlistController;
use App\Http\Controllers\Content\ApplianceController;
use App\Http\Controllers\Content\AreaController;
use App\Http\Controllers\Content\BlogController;
use App\Http\Controllers\Content\BrandController;
use App\Http\Controllers\Content\BrandServiceController;
use App\Http\Controllers\Content\ServiceController;
use App\Http\Controllers\Content\ShopController;
use App\Http\Controllers\Content\VendorController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SetCacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| إعادة توجيه دائم (301) للـ slugs القديمة — من config/redirects.php
|--------------------------------------------------------------------------
| بتتسجّل كـ routes صريحة قبل باقي الراوتس عشان المسارات اللي بتتعارض مع
| راوت ديناميكي (زي /services/{service}) تعمل redirect بدل ما ترجّع 404.
*/
foreach (\App\Support\Redirects::all() as $from => $redirect) {
    Route::redirect('/' . $from, $redirect['to'], $redirect['status']);
}

/*
|--------------------------------------------------------------------------
| الرئيسية + الحجز
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    // عنوان قصير ومركّز للرئيسية (أقل من 60 حرف مع اللاحقة | خدماتي)
    app(\App\Support\Seo::class)
        ->title('صيانة وتركيب تكييفات وأجهزة منزلية')
        ->canonical('/');

    return view('home');
})->name('home');

Route::post('/booking', [BookingController::class, 'store'])
    ->middleware('throttle:6,1') // حماية من السبام: 6 محاولات في الدقيقة
    ->name('booking.store');

/*
|--------------------------------------------------------------------------
| المتجر: السلة + الدفع عند الاستلام + التتبّع (المرحلة 6C)
|--------------------------------------------------------------------------
| صفحات معاملات فيها فورم/session => من غير SetCacheHeaders (مفيش cache كامل)
| وكلها noindex. throttle على الإضافة والـ checkout والتتبّع.
*/
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/add', [CartController::class, 'add'])->middleware('throttle:30,1')->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->middleware('throttle:30,1')->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->middleware('throttle:30,1')->name('cart.remove');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');

Route::get('/order/confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');

// الدفع الأونلاين (المرحلة 6E)
// - webhook: server-to-server، مستثنى من CSRF (bootstrap/app.php)، التأكيد الوحيد للدفع.
// - return: رجوع العميل من البوابة (مجرد تحويل لصفحة التأكيد — مش دليل دفع).
// - retry: إعادة محاولة الدفع للطلب اللي في السيشن.
Route::post('/payments/{gateway}/webhook', [PaymentWebhookController::class, 'webhook'])->name('payment.webhook');
Route::get('/payments/return', [PaymentWebhookController::class, 'return'])->name('payment.return');
Route::get('/payments/retry/{order}', [CheckoutController::class, 'retry'])->middleware('throttle:10,1')->name('payment.retry');
Route::get('/track', [OrderController::class, 'trackForm'])->name('order.track');
Route::post('/track', [OrderController::class, 'track'])->middleware('throttle:15,1')->name('order.track.post');

/*
|--------------------------------------------------------------------------
| حسابات العملاء + المفضلة + المقارنة + تقييمات المنتجات (المرحلة 6D)
|--------------------------------------------------------------------------
| كلها صفحات معاملات/حساب => من غير SetCacheHeaders وكلها noindex (من الكونترولرز).
| guard «customer» منفصل تماماً عن الأدمن.
*/

// دخول/تسجيل/خروج — للضيوف بس (المسجّل بيتحوّل لطلباته).
Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:6,1')->name('register.post');
});
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

// حسابي (طلباتي) — للمسجّلين بس.
Route::middleware('auth:customer')->prefix('account')->name('account.')->group(function () {
    Route::get('/orders', [AccountOrdersController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [AccountOrdersController::class, 'show'])->name('orders.show');
});

// المفضلة — تشتغل للضيف والمسجّل.
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.show');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->middleware('throttle:30,1')->name('wishlist.toggle');
Route::post('/wishlist/remove', [WishlistController::class, 'remove'])->middleware('throttle:30,1')->name('wishlist.remove');

// المقارنة — session، لحد 3 من نفس القسم.
Route::get('/compare', [CompareController::class, 'index'])->name('compare.show');
Route::post('/compare/add', [CompareController::class, 'add'])->middleware('throttle:30,1')->name('compare.add');
Route::post('/compare/remove', [CompareController::class, 'remove'])->middleware('throttle:30,1')->name('compare.remove');
Route::post('/compare/clear', [CompareController::class, 'clear'])->middleware('throttle:30,1')->name('compare.clear');

// تقييم منتج — للمسجّلين المؤهّلين بس.
Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])
    ->middleware('auth:customer', 'throttle:10,1')->name('product.review.store');

/*
|--------------------------------------------------------------------------
| صفحات الموقع التعريفية والوظيفية (SiteController)
|--------------------------------------------------------------------------
*/
Route::middleware(SetCacheHeaders::class)->group(function () {
    Route::get('/about', [SiteController::class, 'about'])->name('about');
    Route::get('/prices', [SiteController::class, 'prices'])->name('prices');
    Route::get('/faq', [SiteController::class, 'faq'])->name('faq');
    Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
    Route::get('/request-service', [SiteController::class, 'requestService'])->name('request-service');
    Route::get('/diagnose', [SiteController::class, 'diagnose'])->name('diagnose');
    Route::get('/reviews', [SiteController::class, 'reviews'])->name('reviews');
});

/*
|--------------------------------------------------------------------------
| sitemap + robots
|--------------------------------------------------------------------------
*/
Route::middleware(SetCacheHeaders::class)->group(function () {
    Route::get('/sitemap.xml', [SitemapController::class, 'index']);
    Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages']);
    Route::get('/sitemap-{type}.xml', [SitemapController::class, 'type'])
        ->where('type', 'services|appliances|areas|brands|brand-services|blog|products|vendors');
    Route::get('/robots.txt', RobotsController::class);
});

/*
|--------------------------------------------------------------------------
| صفحات المحتوى (slugs إنجليزي، محتوى عربي) — فهرس + عنصر مفرد
| route model binding بالـ slug؛ غير المنشور بيرجّع 404.
| Cache-Control خفيف (مفيش فورم في الصفحات دي).
|--------------------------------------------------------------------------
*/
Route::middleware(SetCacheHeaders::class)->group(function () {
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

    Route::get('/appliances', [ApplianceController::class, 'index'])->name('appliances.index');
    Route::get('/appliances/{appliance}', [ApplianceController::class, 'show'])->name('appliances.show');

    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/{area}', [AreaController::class, 'show'])->name('areas.show');

    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('brands.show');
    // صفحة (ماركة × خدمة) — K1. سيجمنتين فمفيش تعارض مع /brands/{brand}.
    Route::get('/brands/{brand}/{service}', [BrandServiceController::class, 'show'])->name('brand-service.show');

    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/category/{category}', [BlogController::class, 'category'])->name('blog.category');
    Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

    // المتجر: هب + فهرس قسم + صفحة منتج (مسار كانوني /shop/{category}/{product}).
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/used', [ShopController::class, 'used'])->name('shop.used');
    Route::get('/shop/{category}', [ShopController::class, 'category'])->name('shop.category');
    Route::get('/shop/{category}/{product}', [ShopController::class, 'show'])->name('shop.show');

    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');

    // صفحات ثابتة (سياسات/تعريفية) — غير المنشورة بترجّع 404.
    Route::get('/pages/{page}', [\App\Http\Controllers\Content\StaticPageController::class, 'show'])->name('pages.show');
});

/*
|--------------------------------------------------------------------------
| Fallback: 301 للـ slugs القديمة (من config/redirects.php) وإلا 404 حقيقي
|--------------------------------------------------------------------------
*/
Route::fallback(function (Request $request) {
    $path = trim($request->path(), '/'); // بدون / في الأول/الآخر
    $map = \App\Support\Redirects::all();

    if (isset($map[$path])) {
        return redirect($map[$path]['to'], $map[$path]['status']);
    }

    abort(404); // بيرجّع status 404 فعلي + صفحة errors/404
});
