<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginCustomerRequest;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Models\Customer;
use App\Support\Seo;
use App\Support\Store\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * دخول/تسجيل/خروج العملاء (المرحلة 6D) — guard «customer» المنفصل.
 *
 * - الدخول بالموبايل أو الإيميل (حقل identifier واحد).
 * - بعد التسجيل/الدخول: ربط الطلبات القديمة بالموبايل + دمج مفضلة الـ session.
 * - كل الصفحات noindex (صفحات حساب).
 */
class CustomerAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('account.orders');
        }

        app(Seo::class)->title('تسجيل الدخول')->canonical('/login')->robots('noindex,nofollow');

        return view('auth.login', ['title' => 'تسجيل الدخول']);
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('account.orders');
        }

        app(Seo::class)->title('إنشاء حساب')->canonical('/register')->robots('noindex,nofollow');

        return view('auth.register', ['title' => 'إنشاء حساب']);
    }

    public function register(RegisterCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $customer = Customer::create([
            'name'     => $data['name'],
            'phone'    => $data['phone'] ?? null,
            'email'    => $data['email'] ?? null,
            'password' => $data['password'],
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        $this->afterAuth($customer);

        return redirect()->intended(route('account.orders'))
            ->with('account_success', 'أهلاً بيك! تم إنشاء حسابك.');
    }

    public function login(LoginCustomerRequest $request): RedirectResponse
    {
        $identifier = trim((string) $request->input('identifier'));
        $credentials = str_contains($identifier, '@')
            ? ['email' => mb_strtolower($identifier)]
            : ['phone' => Customer::normalizePhone($identifier)];

        $credentials['password'] = (string) $request->input('password');
        $remember = (bool) $request->boolean('remember');

        if (! Auth::guard('customer')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'identifier' => 'الموبايل/الإيميل أو كلمة السر مش مظبوطين.',
            ]);
        }

        $request->session()->regenerate();

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $this->afterAuth($customer);

        return redirect()->intended(route('account.orders'))
            ->with('account_success', 'أهلاً بيك تاني.');
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('customer')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('shop.index')->with('account_success', 'تم تسجيل الخروج.');
    }

    /** خطوات مشتركة بعد الدخول/التسجيل: ربط الطلبات + دمج المفضلة. */
    protected function afterAuth(Customer $customer): void
    {
        $customer->linkGuestOrders();
        Wishlist::mergeSessionIntoCustomer($customer);
    }
}
