# -*- coding: utf-8 -*-
"""
مولّد ملفات Google Ads Editor لحملتي صيف 2027 (القاهرة / الجيزة).
التشغيل:  python ads/summer-2027/build_campaigns.py
الناتج: ملفات CSV في ads/summer-2027/import/ — تتستورد في Google Ads Editor
(Account > Import > From file) بالترتيب.

قواعد:
- مفيش ادعاءات غير حقيقية (لا وكيل معتمد، لا ضمان، لا «نفس اليوم»).
- كل مجموعة منطقة/ماركة ليها صفحة هبوط مخصوصة على الموقع.
- المجموعات العامة فيها أسماء المناطق والماركات كلمات سلبية، فالبحث اللي فيه منطقة
  أو ماركة بيروح للمجموعة المخصصة بس.
"""
import csv
import os
import sys

BASE = 'https://khadamaty-ac.com'
PHONE = '01019760562'
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'import')
os.makedirs(OUT, exist_ok=True)

CAMPAIGNS = {
    'KH_AC_Search_Cairo': {'location': 'Cairo Governorate, Egypt'},
    'KH_AC_Search_Giza': {'location': 'Giza Governorate, Egypt'},
}
BUDGET = '500.00'
MAX_CPC = '25.00'   # طلب صاحب الموقع

# ---------------------------------------------------------------- المناطق
# slug, الاسم في الإعلان, صيغ البحث, الحملة, الصفحات المنشورة (خدمة => مسار)
AREAS = [
    # القاهرة
    ('nasr-city', 'مدينة نصر', ['مدينة نصر', 'مدينه نصر'], 'Cairo',
     {'maint': 'ac-maintenance', 'tech': 'ac-repair', 'clean': 'ac-cleaning'}),
    ('tagamoa', 'التجمع', ['التجمع', 'التجمع الخامس', 'التجمع الاول'], 'Cairo',
     {'maint': 'ac-maintenance', 'install': 'ac-installation'}),
    ('new-cairo', 'القاهرة الجديدة', ['القاهرة الجديدة', 'القاهره الجديده'], 'Cairo',
     {'maint': 'ac-maintenance', 'clean': 'ac-cleaning'}),
    ('heliopolis', 'مصر الجديدة', ['مصر الجديدة', 'مصر الجديده', 'الكوربة'], 'Cairo',
     {'maint': 'ac-maintenance', 'install': 'ac-installation'}),
    ('maadi', 'المعادي', ['المعادي', 'زهراء المعادي'], 'Cairo',
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('mokattam', 'المقطم', ['المقطم'], 'Cairo',
     {'maint': 'ac-maintenance', 'clean': 'ac-cleaning'}),
    ('rehab', 'الرحاب', ['الرحاب'], 'Cairo',
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('madinaty', 'مدينتي', ['مدينتي'], 'Cairo',
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('shorouk', 'الشروق', ['الشروق', 'مدينة الشروق'], 'Cairo',
     {'maint': 'ac-maintenance', 'install': 'ac-installation'}),
    ('obour', 'العبور', ['العبور', 'مدينة العبور'], 'Cairo',
     {'maint': 'ac-maintenance'}),
    ('badr', 'بدر', ['مدينة بدر'], 'Cairo',
     {'maint': 'ac-maintenance'}),
    ('new-capital', 'العاصمة الإدارية', ['العاصمة الإدارية', 'العاصمه الاداريه', 'العاصمة الادارية'], 'Cairo',
     {'maint': 'ac-maintenance', 'install': 'ac-installation'}),
    # الجيزة
    ('6th-october', '6 أكتوبر', ['6 اكتوبر', '6 أكتوبر', 'اكتوبر', 'أكتوبر', 'حدائق اكتوبر'], 'Giza',
     {'maint': 'ac-maintenance', 'tech': 'ac-repair', 'install': 'ac-installation', 'clean': 'ac-cleaning'}),
    ('sheikh-zayed', 'الشيخ زايد', ['الشيخ زايد', 'زايد'], 'Giza',
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('faisal', 'فيصل', ['فيصل'], 'Giza',
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('haram', 'الهرم', ['الهرم'], 'Giza',
     {'maint': 'ac-maintenance'}),
    ('hadayek-el-ahram', 'حدائق الأهرام', ['حدائق الاهرام', 'حدائق الأهرام'], 'Giza',
     {'tech': 'ac-repair'}),
    ('dokki', 'الدقي', ['الدقي'], 'Giza',
     {'maint': 'ac-maintenance'}),
    ('mohandessin', 'المهندسين', ['المهندسين'], 'Giza',
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('agouza', 'العجوزة', ['العجوزة', 'العجوزه'], 'Giza', {}),
]

# كلمات كل نوع خدمة (اللي حوّلت السنة اللي فاتت في الأول)
SERVICE_TERMS = {
    'maint': ['صيانة تكييفات', 'صيانه تكييفات', 'صيانة تكييف', 'صيانه تكييف', 'صيانة مكيفات', 'صيانه مكيفات',
              'غسيل تكييف', 'تنظيف تكييف'],
    'tech': ['فني تكييف', 'فني مكيفات', 'فنى تكييف', 'تصليح تكييف', 'اصلاح تكييف', 'فني صيانة مكيفات'],
    'install': ['تركيب تكييف', 'تركيب تكييفات', 'تركيب مكيفات', 'فك ونقل تكييف', 'نقل تكييف',
                'فك وتركيب تكييف', 'تأسيس تكييف', 'تاسيس تكييف', 'تأسيس مواسير تكييف'],
}
SERVICE_LABEL = {'maint': 'صيانة تكييفات', 'tech': 'فني تكييف', 'install': 'تركيب تكييفات'}
SERVICE_FALLBACK = {'maint': 'ac-maintenance', 'tech': 'ac-repair', 'install': 'ac-installation'}

# ---------------------------------------------------------------- الماركات
# slug, الاسم, صيغ البحث, الصفحات المنشورة
BRANDS = [
    ('carrier', 'كاريير', ['كاريير', 'كارير', 'كريير', 'carrier'],
     {'maint': 'ac-maintenance', 'tech': 'ac-repair', 'install': 'ac-installation'}),
    ('sharp', 'شارب', ['شارب', 'sharp'],
     {'maint': 'ac-maintenance', 'tech': 'ac-repair', 'install': 'ac-installation'}),
    ('midea', 'ميديا', ['ميديا', 'midea'],
     {'maint': 'ac-maintenance', 'tech': 'ac-repair', 'install': 'ac-installation'}),
    ('union-air', 'يونيون إير', ['يونيون اير', 'يونيون إير', 'يونيون'],
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('fresh', 'فريش', ['فريش', 'fresh'],
     {'maint': 'ac-maintenance', 'tech': 'ac-repair', 'install': 'ac-installation'}),
    ('haier', 'هاير', ['هاير', 'haier'],
     {'maint': 'ac-maintenance', 'install': 'ac-installation'}),
    ('tornado', 'تورنيدو', ['تورنيدو', 'tornado'],
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('gree', 'جري', ['جري', 'gree'],
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('lg', 'إل جي', ['ال جي', 'lg'],
     {'maint': 'ac-maintenance', 'tech': 'ac-repair'}),
    ('york', 'يورك', ['يورك', 'york'],
     {'maint': 'ac-maintenance'}),
]
BRANDS = [(s, n, [v for v in vs if v.isascii() or all('؀' <= ch <= 'ۿ' or ch == ' ' for ch in v)], p)
          for s, n, vs, p in BRANDS]
BRAND_TERMS = {
    'maint': ['صيانة تكييف', 'صيانه تكييف', 'صيانة مكيف', 'غسيل تكييف', 'تنظيف تكييف'],
    'tech': ['تصليح تكييف', 'اصلاح تكييف', 'فني تكييف', 'اعطال تكييف'],
    'install': ['تركيب تكييف', 'فك ونقل تكييف', 'فك وتركيب تكييف'],
}

# ---------------------------------------------------------------- المجموعات العامة
GENERIC = [
    ('Gen_Maintenance', 'ac-maintenance', 'صيانة تكييفات',
     ['صيانة تكييفات', 'صيانه تكييفات', 'صيانة مكيفات', 'صيانه مكيفات', 'صيانة تكييف', 'صيانه تكييف',
      'صيانة التكييف', 'صيانة تكييف سبليت', 'صيانة تكييفات بالمنزل', 'شركة صيانة تكييفات']),
    ('Gen_Technician', 'ac-repair', 'فني تكييف',
     ['فني مكيفات', 'فني تكييف', 'فنى تكييف', 'فني صيانة مكيفات', 'فني تكييفات', 'تصليح تكييف',
      'تصليح مكيفات', 'اصلاح تكييف', 'اصلاح اعطال التكييف', 'التكييف مش بيبرد']),
    ('Gen_Cleaning', 'ac-cleaning', 'غسيل تكييفات',
     ['غسيل تكييف', 'غسيل تكييفات', 'تنظيف تكييف', 'تنظيف التكييف', 'غسيل مكيفات', 'غسيل تكييف سبليت']),
    ('Gen_Installation', 'ac-installation', 'تركيب تكييفات',
     ['تركيب تكييف', 'تركيب تكييفات', 'تركيب مكيفات', 'تركيب تكييف سبليت', 'سعر تركيب تكييف', 'فني تركيب تكييف', 'تركيب تكييف جديد', 'تركيب تكييف كونسيلد']),
    ('Gen_GasRefill', 'ac-gas-refill', 'شحن فريون',
     ['شحن فريون', 'شحن فريون تكييف', 'شحن غاز تكييف', 'شحن فريون التكييف', 'سعر شحن فريون']),
    ('Gen_Leak', 'ac-leak-repair', 'إصلاح تسريب التكييف',
     ['تسريب تكييف', 'تسريب فريون', 'التكييف بينقط مية', 'تنقيط التكييف', 'التكييف بينزل مية']),
    ('Gen_Relocation', 'ac-relocation', 'فك ونقل تكييف',
     ['فك ونقل تكييف', 'نقل تكييف', 'فك تكييف', 'فك وتركيب تكييف', 'نقل تكييف من مكان الى اخر', 'فك ونقل تكييفات', 'نقل مكيفات', 'فك وتركيب مكيفات', 'سعر فك وتركيب تكييف', 'سعر نقل تكييف']),
    ('Gen_Piping', 'ac-piping-installation', 'تأسيس مواسير التكييف',
     ['تأسيس تكييف', 'تاسيس تكييف', 'تأسيس مواسير تكييف', 'تاسيس مواسير التكييف', 'تأسيس نحاس تكييف', 'تأسيس تكييف سبليت', 'تأسيس تكييف كونسيلد', 'سعر تأسيس تكييف', 'تاسيس مواسير تكييف']),
    ('Gen_Central', 'central-ac-maintenance', 'صيانة تكييف مركزي',
     ['صيانة تكييف مركزي', 'صيانة التكييف المركزي', 'صيانة تكييف كونسيلد', 'فني تكييف مركزي', 'مكيفات دكت']),
]

# ---------------------------------------------------------------- كلمات سلبية (مستوى الحملة)
NEGATIVES = [
    # بحث عن الوكيل/الشركة نفسها
    'الموقع الرسمي', 'موقع رسمي', 'الصفحة الرسمية', 'صفحة رسمية', 'توكيل', 'التوكيل', 'الوكيل', 'وكيل',
    'خدمة عملاء', 'خدمه عملاء', 'الخط الساخن', 'خط ساخن', 'المجانية', 'مجانا', 'مجاني', 'ببلاش',
    'مبيعات', 'فرع', 'فروع',
    # كتالوج وكتب
    'كتالوج', 'pdf', 'كتاب', 'شرح', 'كورس', 'دورة', 'تعليم', 'يوتيوب', 'فيديو',
    # بيع وشراء أجهزة
    'للبيع', 'بيع', 'شراء', 'اشتري', 'مستعمل', 'مستعملة', 'مستعمله', 'سعر تكييف', 'اسعار تكييفات',
    'أسعار تكييفات', 'اسعار التكييفات', 'عروض تكييفات', 'تقسيط', 'افضل تكييف', 'أفضل تكييف',
    'افضل انواع التكييفات', 'تكييفات للبيع', 'معرض', 'محلات تكييف', 'محلات تكييفات',
    # قطع غيار وإكسسوارات
    'قطع غيار', 'قطع الغيار', 'ريموت', 'خرطوم', 'كمبروسر للبيع', 'محل قطع',
    # أنواع خارج النطاق
    'صحراوي', 'صحراوى', 'الصحراوي', 'متنقل', 'المتنقل', 'سيارة', 'سيارات', 'عربية',
    'ثلاجة', 'ثلاجات', 'غسالة', 'غسالات',
    # وظائف
    'وظائف', 'وظيفة', 'مطلوب', 'تدريب',
]
NEGATIVE_EXACT_PHRASES = ['رقم صيانة كاريير', 'رقم كاريير', 'رقم شارب', 'رقم صيانة شارب', 'رقم ميديا',
                          'رقم يونيون اير', 'رقم فريش', 'موقع كاريير']

# ---------------------------------------------------------------- مساعدات

def L(s):
    return len(s)


def fit(items, limit):
    """بيرجّع العناصر اللي في الحد بس، من غير تكرار."""
    out, seen = [], set()
    for x in items:
        x = ' '.join(x.split())
        if x and L(x) <= limit and x not in seen:
            out.append(x)
            seen.add(x)
    return out


COMMON_HEADLINES = [
    'الدفع بعد الشغل', 'كشف بالقياس قبل الإصلاح', 'التكلفة قبل أي شغل', 'لكل ماركات التكييف',
    'فني بيوصلك البيت', 'سبليت وشباك وكونسيلد', 'اتصل الآن', 'غسيل بماكينة الغسيل',
    'جرّب التبريد قبل ما تدفع',
]
COMMON_DESCS = [
    'فني بيكشف ويقيس الأول، وبيقولك العطل والتكلفة قبل أي شغل. والدفع بعد ما تجرّب التبريد.',
    'صيانة وغسيل بالماكينة للوحدتين، وقياس الغاز والكهربا قبل وبعد. اتصل واحجز معادك.',
    'بنتعامل مع كل الماركات: سبليت وشباك وكونسيلد. اتصل الآن وقول المنطقة وعدد الأجهزة.',
]
PRICE_HEADLINE = 'صيانة 450 ج.م للتكييف'


def rsa(campaign, ad_group, url, path1, path2, pinned, extra_h, descs):
    heads = fit([pinned] + extra_h + COMMON_HEADLINES, 30)
    if heads[0] != pinned:
        raise SystemExit(f'pinned headline too long: {pinned} ({L(pinned)})')
    heads = heads[:15]
    if len(heads) < 3:
        raise SystemExit(f'not enough headlines for {ad_group}')
    ds = fit(descs + COMMON_DESCS, 90)[:4]
    row = {'Campaign': campaign, 'Ad Group': ad_group, 'Ad Type': 'Responsive search ad', 'Status': 'Paused',
           'Final URL': url, 'Path 1': path1[:15], 'Path 2': path2[:15]}
    for i in range(15):
        row[f'Headline {i+1}'] = heads[i] if i < len(heads) else ''
        row[f'Headline {i+1} position'] = '1' if i == 0 else ''
    for i in range(4):
        row[f'Description {i+1}'] = ds[i] if i < len(ds) else ''
        row[f'Description {i+1} position'] = ''
    return row


def path(s):
    return s.replace(' ', '-')[:15]


# ---------------------------------------------------------------- البناء
campaign_rows, ad_group_rows, kw_rows, neg_rows, agneg_rows, ad_rows = [], [], [], [], [], []
loc_rows, sched_rows, dev_rows, sl_rows, co_rows, ss_rows, call_rows = [], [], [], [], [], [], []

area_variants_all = sorted({v for a in AREAS for v in a[2]}, key=len, reverse=True)
brand_variants_all = sorted({v for b in BRANDS for v in b[2]}, key=len, reverse=True)

for camp, cfg in CAMPAIGNS.items():
    region = 'Cairo' if 'Cairo' in camp else 'Giza'
    campaign_rows.append({'Campaign': camp, 'Campaign Type': 'Search', 'Campaign Status': 'Paused',
                          'Budget': BUDGET, 'Budget Type': 'Daily', 'Bid Strategy Type': 'Maximize clicks',
                          'Bid Limit': MAX_CPC, 'Networks': 'Google search;Search Partners off',
                          'Languages': 'Arabic;English'})
    loc_rows.append({'Campaign': camp, 'Location': cfg['location'], 'Status': 'Enabled'})
    days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    sched_rows.append({'Campaign': camp, 'Ad Schedule': ';'.join(f'({d}[09:00-22:00])' for d in days)})
    dev_rows.append({'Campaign': camp, 'Mobile Bid Modifier': '0%', 'Desktop Bid Modifier': '-40%',
                     'Tablet Bid Modifier': '-50%'})
    call_rows.append({'Campaign': camp, 'Country': 'EG', 'Phone Number': PHONE, 'Status': 'Paused'})

    for kw in NEGATIVES:
        neg_rows.append({'Campaign': camp, 'Keyword': kw, 'Criterion Type': 'Campaign Negative Phrase'})
    for kw in NEGATIVE_EXACT_PHRASES:
        neg_rows.append({'Campaign': camp, 'Keyword': kw, 'Criterion Type': 'Campaign Negative Phrase'})

    # ---- المناطق
    for slug, name, variants, reg, pages in AREAS:
        if reg != region:
            continue
        for svc in ('maint', 'tech', 'install'):
            # الصفحة: منطقة×خدمة لو منشورة، وإلا صفحة المنطقة
            page_slug = pages.get(svc)
            url = f'{BASE}/areas/{slug}/{page_slug}' if page_slug else f'{BASE}/areas/{slug}'
            ag = f'Area_{slug}_{svc}'
            ad_group_rows.append({'Campaign': camp, 'Ad Group': ag, 'Ad Group Status': 'Paused',
                                  'Max CPC': MAX_CPC, 'Ad Group Type': 'Standard'})
            for v in variants:
                for t in SERVICE_TERMS[svc]:
                    for kw in (f'{t} {v}', f'{t} في {v}') if svc != 'install' else (f'{t} {v}',):
                        for mt in ('Exact', 'Phrase'):
                            kw_rows.append({'Campaign': camp, 'Ad Group': ag, 'Keyword': kw, 'Criterion Type': mt,
                                            'Status': 'Paused', 'Final URL': url})
            label = SERVICE_LABEL[svc]
            pinned = f'{label} {name}'
            if L(pinned) > 30:
                pinned = f'{label.split()[0]} تكييف {name}'
            extra = [
                f'{label} في {name}',
                f'فني تكييف {name}' if svc != 'tech' else f'تصليح تكييف {name}',
                f'فني مكيفات {name}',
                PRICE_HEADLINE if svc == 'maint' else 'الكشف قبل أي إصلاح',
                'صيانة وغسيل وتصليح وتركيب',
                f'بنغطي {name} وحواليها',
            ]
            descs = [
                f'{label} في {name}: فني بيوصلك، وبيقيس ويقولك التكلفة قبل الشغل. والدفع بعد الخدمة.',
                f'خدمة تكييف في {name} لكل الماركات. اتصل الآن وقول العنوان وعدد الأجهزة ونأكد معادك.',
            ]
            ad_rows.append(rsa(camp, ag, url, path(label.split()[0]), path(name), pinned, extra, descs))

    # ---- الماركات (في الحملتين، والحملة بتحدد المكان)
    for slug, name, variants, pages in BRANDS:
        for svc in ('maint', 'tech', 'install'):
            page_slug = pages.get(svc) or SERVICE_FALLBACK[svc]
            url = f'{BASE}/brands/{slug}/{page_slug}' if pages.get(svc) else f'{BASE}/brands/{slug}'
            ag = f'Brand_{slug}_{svc}'
            ad_group_rows.append({'Campaign': camp, 'Ad Group': ag, 'Ad Group Status': 'Paused',
                                  'Max CPC': MAX_CPC, 'Ad Group Type': 'Standard'})
            for v in variants:
                for t in BRAND_TERMS[svc]:
                    for mt in ('Exact', 'Phrase'):
                        kw_rows.append({'Campaign': camp, 'Ad Group': ag, 'Keyword': f'{t} {v}', 'Criterion Type': mt,
                                        'Status': 'Paused', 'Final URL': url})
            verb = {'maint': 'صيانة تكييف', 'tech': 'تصليح تكييف', 'install': 'تركيب تكييف'}[svc]
            pinned = f'{verb} {name}'
            extra = [f'فني تكييف {name}', f'{name} سبليت وشباك', PRICE_HEADLINE if svc == 'maint' else 'الكشف قبل أي إصلاح',
                     'مركز صيانة مستقل']
            descs = [
                f'{verb} {name}: فني بيكشف ويقيس، وبيقولك العطل والتكلفة قبل الشغل. الدفع بعد الخدمة.',
                f'بنصلّح ونصون تكييفات {name} وكل الماركات. مركز صيانة مستقل. اتصل الآن واحجز معادك.',
            ]
            ad_rows.append(rsa(camp, ag, url, path(verb.split()[0]), path(name), pinned, extra, descs))
            # إعلان احتياطي من غير اسم الماركة (لو جوجل رفض اسم العلامة التجارية)
            ad_rows.append(rsa(camp, ag, url, path(verb.split()[0]), 'كل-الماركات',
                               f'{verb} لكل الماركات', ['فني تكييف لكل الماركات', 'مركز صيانة مستقل'],
                               [f'{verb} لكل الماركات: فني بيكشف ويقيس ويقولك التكلفة قبل الشغل. الدفع بعد الخدمة.']))

    # ---- العام
    for ag_name, svc_slug, label, kws in GENERIC:
        ag = ag_name
        # البحث العام (من غير منطقة ولا ماركة) بيروح للصفحة الرئيسية — طلب صاحب الموقع.
        url = f'{BASE}/'
        ad_group_rows.append({'Campaign': camp, 'Ad Group': ag, 'Ad Group Status': 'Paused',
                              'Max CPC': MAX_CPC, 'Ad Group Type': 'Standard'})
        for kw in kws:
            for mt in ('Exact', 'Phrase'):
                kw_rows.append({'Campaign': camp, 'Ad Group': ag, 'Keyword': kw, 'Criterion Type': mt,
                                'Status': 'Paused', 'Final URL': url})
        # توجيه: أي بحث فيه منطقة أو ماركة يروح للمجموعة المخصصة
        for v in area_variants_all + brand_variants_all:
            agneg_rows.append({'Campaign': camp, 'Ad Group': ag, 'Keyword': v, 'Criterion Type': 'Negative Phrase'})
        pinned = label if L(label) <= 30 else label[:30]
        extra = {
            'Gen_Maintenance': ['صيانة مكيفات بالبيت', PRICE_HEADLINE, 'تكييفين بـ 800 ج.م'],
            'Gen_Technician': ['فني مكيفات', 'فني تكييف ومكيفات', 'الكشف 500 ج.م', 'تصليح تكييف بالقياس'],
            'Gen_Cleaning': ['غسيل بماكينة الغسيل', 'غسيل 450 ج.م للتكييف', 'تكييفين بـ 800 ج.م'],
            'Gen_Installation': ['تركيب تكييف 1200 ج.م', 'فاكيوم وكشف تسريب'],
            'Gen_GasRefill': ['شحن فريون التكييف', 'كشف التسريب قبل الشحن', 'R22 و R410A و R32'],
            'Gen_Leak': ['إصلاح تسريب التكييف', 'غاز أو مية من المصدر', 'الكشف 500 ج.م'],
            'Gen_Relocation': ['فك ونقل التكييف', 'من غير ضياع الغاز', 'فاكيوم وتجربة تشغيل'],
            'Gen_Piping': ['تأسيس قبل المحارة', 'اختبار ضغط قبل الدفن', 'نحاس وعزل وصرف'],
            'Gen_Central': ['صيانة الكونسيلد', 'فلتر الراجع والدكت', 'تكييف مركزي وكونسيلد'],
        }[ag_name]
        descs = [f'{label}: فني بيوصلك، وبيقيس ويقولك التكلفة قبل الشغل. والدفع بعد ما تجرّب.']
        ad_rows.append(rsa(camp, ag, url, path(label.split()[0]), path(label.split()[-1]), pinned, extra, descs))

    # ---- الإضافات
    for text, d1, d2, slug in [
        ('صيانة 450 ج.م', 'كشف وغسيل بالماكينة', 'تكييفين بـ 800 ج.م', '/services/ac-maintenance'),
        ('فني مكيفات', 'تصليح الأعطال بالقياس', 'التكلفة قبل الشغل', '/services/ac-repair'),
        ('غسيل تكييفات', 'غسيل بماكينة الغسيل', 'للوحدتين وتسليك الصرف', '/services/ac-cleaning'),
        ('تركيب تكييف', 'فاكيوم وكشف تسريب', 'وتجربة تشغيل', '/services/ac-installation'),
        ('شحن فريون', 'كشف التسريب الأول', 'بالنوع والكمية الصح', '/services/ac-gas-refill'),
        ('مناطق الخدمة', 'القاهرة والجيزة', 'شوف منطقتك', '/areas'),
    ]:
        sl_rows.append({'Campaign': camp, 'Sitelink Text': text, 'Description Line 1': d1,
                        'Description Line 2': d2, 'Final URL': BASE + slug, 'Status': 'Paused'})
    for c in ['الدفع بعد الشغل', 'التكلفة قبل أي شغل', 'كشف بالقياس', 'لكل الماركات', 'غسيل بماكينة الغسيل',
              'فني بيوصلك البيت', 'صيانة 450 ج.م']:
        co_rows.append({'Campaign': camp, 'Callout Text': c, 'Status': 'Paused'})
    ss_rows.append({'Campaign': camp, 'Header': 'Services',
                    'Values': 'صيانة;غسيل;تصليح;شحن فريون;تركيب;فك ونقل;تأسيس مواسير', 'Status': 'Paused'})
    ss_rows.append({'Campaign': camp, 'Header': 'Brands',
                    'Values': 'كاريير;شارب;ميديا;يونيون إير;فريش;هاير;تورنيدو;جري', 'Status': 'Paused'})

# ---------------------------------------------------------------- فحوص
problems = []
for r in sl_rows:
    if L(r['Sitelink Text']) > 25 or L(r['Description Line 1']) > 35 or L(r['Description Line 2']) > 35:
        problems.append(('sitelink', r['Sitelink Text']))
for r in co_rows:
    if L(r['Callout Text']) > 25:
        problems.append(('callout', r['Callout Text']))
for r in ss_rows:
    for v in r['Values'].split(';'):
        if L(v) > 25:
            problems.append(('snippet', v))
for r in kw_rows:
    if L(r['Keyword']) > 80 or len(r['Keyword'].split()) > 10:
        problems.append(('keyword', r['Keyword']))
if problems:
    print('PROBLEMS:', problems)
    sys.exit(1)

# كلمات مكررة في نفس المجموعة
seen = set()
kw_rows_u = []
for r in kw_rows:
    k = (r['Campaign'], r['Ad Group'], r['Keyword'], r['Criterion Type'])
    if k not in seen:
        seen.add(k)
        kw_rows_u.append(r)


def write(name, rows):
    if not rows:
        return
    with open(os.path.join(OUT, name), 'w', encoding='utf-8-sig', newline='') as f:
        w = csv.DictWriter(f, fieldnames=list(rows[0].keys()))
        w.writeheader()
        w.writerows(rows)


write('01_campaigns.csv', campaign_rows)
write('02_ad_groups.csv', ad_group_rows)
write('03_keywords.csv', kw_rows_u)
write('04_campaign_negative_keywords.csv', neg_rows)
write('05_ad_group_negative_keywords.csv', agneg_rows)
write('06_responsive_search_ads.csv', ad_rows)
write('07_locations.csv', loc_rows)
write('08_ad_schedule.csv', sched_rows)
write('09_device_bid_adjustments.csv', dev_rows)
write('10_sitelinks.csv', sl_rows)
write('11_callouts.csv', co_rows)
write('12_structured_snippets.csv', ss_rows)
write('13_call_assets.csv', call_rows)

print('campaigns', len(campaign_rows), '| ad groups', len(ad_group_rows), '| keywords', len(kw_rows_u),
      '| campaign negatives', len(neg_rows), '| ad-group negatives', len(agneg_rows), '| ads', len(ad_rows))
