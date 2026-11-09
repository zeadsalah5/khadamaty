<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * صفحات الموقع التعريفية والوظيفية (مش مبنية على موديل محتوى):
 * about / prices / faq / contact / request-service / diagnose / reviews.
 * كل method بتضبط الـ SEO عبر خدمة Seo المركزية وترجّع الـ view.
 */
class SiteController extends Controller
{
    protected function seo(): Seo
    {
        return app(Seo::class);
    }

    /** مسار التصفّح القياسي (الرئيسية + الصفحة الحالية). */
    protected function crumbs(string $label, string $path): array
    {
        return [
            ['label' => 'الرئيسية', 'url' => '/'],
            ['label' => $label, 'url' => $path],
        ];
    }

    public function about(): View
    {
        $this->seo()
            ->title('مين إحنا — خدمة وصيانة تكييفات وأجهزة')
            ->description('خدماتي فريق فنيين متخصصين في صيانة وتركيب وإصلاح التكييفات والأجهزة في ' . site('areas_label') . '. اعرف طريقتنا في الشغل وليه تختارنا.')
            ->canonical('/about')
            ->robots('index,follow');

        return view('pages.about', [
            'breadcrumbs' => $this->crumbs('مين إحنا', '/about'),
        ]);
    }

    public function prices(): View
    {
        $this->seo()
            ->title('الأسعار والباقات — صيانة وتركيب تكييفات')
            ->description('باقات وأسعار واضحة لصيانة وغسيل وشحن وتركيب التكييفات. السعر النهائي بيتأكد وقت المعاينة، والدفع بعد الشغل.')
            ->canonical('/prices')
            ->robots('index,follow');

        return view('pages.prices', [
            'breadcrumbs' => $this->crumbs('الأسعار', '/prices'),
        ]);
    }

    public function faq(): View
    {
        $this->seo()
            ->title('الأسئلة الشائعة — المواعيد والدفع والضمان')
            ->description('إجابات على أكتر الأسئلة المتكررة عن مواعيد الوصول، الدفع بعد الشغل، الضمان، والماركات اللي بنصلّحها.')
            ->canonical('/faq')
            ->robots('index,follow');

        return view('pages.faq', [
            'faqs'        => \App\Support\Catalog::faqs(),
            'breadcrumbs' => $this->crumbs('الأسئلة الشائعة', '/faq'),
        ]);
    }

    public function contact(): View
    {
        $this->seo()
            ->title('اتصل بينا — احجز أو اسأل عن خدمتك')
            ->description('كلّمنا على ' . site('phone') . ' أو على واتساب، أو ابعتلنا طلب خدمة. بنخدم ' . site('areas_label') . ' والدفع بعد الشغل.')
            ->canonical('/contact')
            ->robots('index,follow');

        return view('pages.contact', [
            'breadcrumbs' => $this->crumbs('اتصل بينا', '/contact'),
        ]);
    }

    public function requestService(Request $request): View
    {
        // اختيار الخدمة مبدئياً لو جت في الكويري (?service=...) ومطابقة للقايمة.
        $preselect = $request->query('service');
        if (! in_array($preselect, (array) site('booking_services', []), true)) {
            $preselect = null;
        }

        $this->seo()
            ->title('اطلب خدمة — احجز فني تكييفات وأجهزة')
            ->description('املأ فورم الطلب: اختار الخدمة ونوع الجهاز والمنطقة والوقت المناسب، وهنتواصل معاك نأكد المعاد. الدفع بعد الشغل.')
            ->canonical('/request-service')
            ->robots('index,follow');

        return view('pages.request-service', [
            'preselect'   => $preselect,
            'breadcrumbs' => $this->crumbs('اطلب خدمة', '/request-service'),
        ]);
    }

    public function diagnose(): View
    {
        $this->seo()
            ->title('شخّص عطل التكييف — خطوة بخطوة')
            ->description('مش عارف المشكلة فين؟ اختار العرض اللي بيحصل، وهنقولك الأسباب المحتملة والخدمة المناسبة — وتقدر تحجز على طول.')
            ->canonical('/diagnose')
            ->robots('index,follow');

        return view('pages.diagnose', [
            'breadcrumbs' => $this->crumbs('تشخيص عطل', '/diagnose'),
        ]);
    }

    /** حاسبة قدرة التكييف المناسب للغرفة (أداة تفاعلية + دليل) — النتيجة تقديرية. */
    public function acCalculator(): View
    {
        $this->seo()
            ->title('حاسبة قدرة التكييف المناسب للغرفة (بالحصان)')
            ->description('أدخل أبعاد غرفتك ودرجة تعرّضها للشمس، واعرف قدرة التكييف المناسبة بالحصان تقريباً. أداة مجانية + دليل اختيار. النتيجة تقديرية وبتتأكد بالمعاينة.')
            ->canonical('/ac-calculator')
            ->robots('index,follow');

        // أسئلة شائعة للصفحة (تتعرض + schema).
        $faqs = [
            ['q' => 'إزاي بحسب قدرة التكييف المناسبة للغرفة؟', 'a' => 'بتحسب مساحة الغرفة (الطول × العرض)، وتضربها في معامل حِمل حراري يراعي ارتفاع السقف وتعرّض الغرفة للشمس وعدد الأفراد، فيطلع لك الحمل التقريبي بالـ BTU، وبعدها بيتحوّل لأقرب قدرة بالحصان. الحاسبة بتعمل ده تلقائياً، والمعاينة الفنية بتأكّد الرقم.'],
            ['q' => 'كام حصان يكفي غرفة 20 متر؟', 'a' => 'كتقدير مبدئي، غرفة حوالي 20 متر مربع بسقف وتعرّض عاديين بتحتاج قريب من 2.25 حصان. لكن التعرّض للشمس وارتفاع السقف وعدد الأفراد ممكن يرفعوا الاحتياج — عشان كده الرقم تقديري والمعاينة بتحسمه.'],
            ['q' => 'هل اختيار مكيف أكبر من اللازم أحسن؟', 'a' => 'لأ. المكيف الأكبر من اللازم بيبرّد بسرعة ويفصل كتير، فبيصرف كهربا أكتر ومبيسحبش الرطوبة كويس. والأصغر من اللازم بيفضل شغّال من غير ما يبرّد. الأنسب هو القدرة المطابقة لحجم الغرفة وظروفها.'],
            ['q' => 'النتيجة دي نهائية؟', 'a' => 'لأ، النتيجة تقديرية بتساعدك تختار الأقرب. عوامل زي جودة العزل، عدد وحجم النوافذ، واتجاه الغرفة بتأثر كمان — فبننصح بمعاينة فنية قبل الشراء أو التركيب.'],
        ];

        return view('pages.ac-calculator', [
            'breadcrumbs' => $this->crumbs('حاسبة قدرة التكييف', '/ac-calculator'),
            'faqs' => $faqs,
        ]);
    }

    /**
     * صفحة الضمان — مسودة noindex دايماً، محتواها من إعدادات الضمان.
     * (من غير schema للضمان حسب القرار.)
     */
    public function warranty(): View
    {
        $this->seo()
            ->title('الضمان على الخدمة')
            ->description('تفاصيل الضمان على خدمات صيانة وتركيب التكييفات والأجهزة.')
            ->canonical('/warranty')
            ->noindex(); // مسودة — noindex لحد ما صاحب الموقع يراجعها

        return view('pages.warranty', [
            'breadcrumbs' => $this->crumbs('الضمان', '/warranty'),
        ]);
    }

    public function reviews(): View
    {
        $reviews = Review::query()->approved()->latest()->get();

        // فاضي => noindex,follow ومش بيدخل السايت ماب (متضافش في SitemapController).
        $this->seo()
            ->title('آراء العملاء — تجارب حقيقية')
            ->description('آراء وتقييمات عملائنا عن خدمات صيانة وتركيب التكييفات والأجهزة.')
            ->canonical('/reviews')
            ->robots($reviews->isEmpty() ? 'noindex,follow' : 'index,follow');

        return view('pages.reviews', [
            'reviews'     => $reviews,
            'breadcrumbs' => $this->crumbs('آراء العملاء', '/reviews'),
        ]);
    }
}
