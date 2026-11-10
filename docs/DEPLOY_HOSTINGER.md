# دليل رفع «خدماتي» على استضافة Hostinger (خطوة بخطوة)

> موقع Laravel 12 — استضافة Hostinger مشتركة (Premium Web Hosting / hPanel).
> اتبع الخطوات بالترتيب. أي خطوة فيها `php artisan` أو `composer` تتعمل عبر **SSH**.

---

## نظرة سريعة على المتطلبات
- **PHP 8.2** أو أحدث، مع الإكستنشنز: `bcmath, ctype, curl, dom, fileinfo, gd, mbstring, openssl, pdo_mysql, tokenizer, xml, zip`.
- **قاعدة بيانات MySQL**.
- **Composer** (متاح على Hostinger).
- **الأصول مبنية** في `public/build` (مبنية محلياً بـ `npm run build` — الاستضافة المشتركة مفيهاش Node).

---

## الخطوة 1 — نسخة PHP
hPanel → **متقدم → PHP Configuration**:
1. تاب **PHP version** → اختار **8.2** (أو 8.3).
2. تاب **PHP extensions** → فعّل الإكستنشنز المذكورة فوق.
3. احفظ.

---

## الخطوة 2 — قاعدة بيانات MySQL
hPanel → **قواعد البيانات → قواعد بيانات MySQL**:
1. أنشئ: اسم قاعدة (مثلاً `khadamaty`)، مستخدم، وكلمة مرور قوية (زرار «توليد»).
2. **سجّل الـ 4 دول** (هتحطهم في `.env`):
   - اسم القاعدة الكامل (بالبادئة، زي `u123456789_khadamaty`)
   - اسم المستخدم الكامل (بالبادئة)
   - كلمة المرور
   - الـ Host = `localhost`

---

## الخطوة 3 — تفعيل SSH
hPanel → **متقدم → SSH Access** → فعّله. اكتب بيانات الدخول (host, port, username).

من جهازك، اتصل:
```bash
ssh USERNAME@HOST -p PORT
```

---

## الخطوة 4 — رفع الكود
الدليل بيفترض إن مجلد الدومين على Hostinger هو:
`~/domains/YOUR_DOMAIN.com/` وجواه `public_html` (ده اللي الدومين بيخدم منه).

### الطريقة (أ) — عبر SSH + Git (الأفضل)
```bash
cd ~/domains/YOUR_DOMAIN.com
git clone https://github.com/zeadsalah5/khadamaty.git app
cd app
```
> لو الريبو private هيطلب username + **Personal Access Token** (مش الباسورد). اعمل توكن من GitHub: Settings → Developer settings → Personal access tokens.
> للرفع من فرع معيّن: `git clone -b feature/hero-glass https://github.com/zeadsalah5/khadamaty.git app`

### الطريقة (ب) — رفع ZIP (لو مش مرتاح للـ Git)
1. من جهازك اعمل ZIP للمشروع **من غير** `node_modules` و`.git` (بس **مع** `vendor` و`public/build`).
2. ارفعه عبر File Manager في hPanel داخل `~/domains/YOUR_DOMAIN.com/app` وفك الضغط.
3. لو رفعت `vendor` بالفعل، تقدر تتخطى `composer install` تحت.

---

## الخطوة 5 — تنصيب الباكدجات (SSH)
```bash
cd ~/domains/YOUR_DOMAIN.com/app
composer install --no-dev --optimize-autoloader
```
> لو `composer` مش متعرّف، جرّب `php composer.phar install ...` أو `php -d memory_limit=-1 /usr/bin/composer install ...`.

---

## الخطوة 6 — ملف البيئة `.env`
```bash
cp .env.example .env
nano .env
```
عدّل القيم دي على الأقل:
```ini
APP_NAME=خدماتي
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_DOMAIN.com
CANONICAL_HOST=YOUR_DOMAIN.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_khadamaty
DB_USERNAME=u123456789_khadamaty
DB_PASSWORD=الباسورد-بتاعك

# لو ورا Cloudflare فقط (غير كده سيبها فاضية):
# TRUSTED_PROXIES=
```
احفظ (Ctrl+O ثم Enter ثم Ctrl+X).

ثم:
```bash
php artisan key:generate
```

---

## الخطوة 7 — قاعدة البيانات والأدمن (SSH)
```bash
php artisan migrate --force
php artisan db:seed --force        # أول مرة بس — يملأ المحتوى الأولي
php artisan khadamaty:make-admin   # أمر تفاعلي: ينشئ حساب أدمن للوحة /admin
php artisan storage:link
```

---

## الخطوة 8 — كاش الأداء (SSH)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> **مهم:** أي مرة تعدّل `.env` بعد كده، شغّل `php artisan config:clear` ثم `config:cache` تاني.

---

## الخطوة 9 — توجيه الدومين لمجلد `public` (أهم خطوة)
لارافيل لازم الدومين يخدم من مجلد `public` بس. على Hostinger المشترك، أسهل طريقة مضمونة:

**اربط `public_html` بمجلد `public` بتاع لارافيل عبر symlink:**
```bash
cd ~/domains/YOUR_DOMAIN.com
# خُد نسخة احتياطية من public_html الافتراضي (لو فيه ملفات)
mv public_html public_html_backup
# اعمل رابط رمزي يخلّي public_html = مجلد public بتاع التطبيق
ln -s app/public public_html
```
> لو Hostinger مبيسمحش بـ symlink لـ public_html، البديل:
> 1. انقل **محتويات** `app/public/` لـ `public_html/`.
> 2. عدّل `public_html/index.php` وخلّي المسارين دول يشاوروا على مجلد app:
> ```php
> require __DIR__.'/../app/vendor/autoload.php';
> $app = require_once __DIR__.'/../app/bootstrap/app.php';
> ```
> 3. تأكد إن `public_html/.htaccess` موجود (موجود جوه public الأصلي).

---

## الخطوة 10 — الأصول والصور
- **public/build** (CSS/JS): لازم يكون اترفع مع الكود (مبني محلياً). لو مش موجود على السيرفر، ارفعه يدوياً من `public/build` المحلي عبر File Manager/SFTP داخل `app/public/build`.
- **الصور** (`public/images/*`): دي صور الداشبورد — هترفعها من `/admin` بعد التشغيل (أو ترفع مجلد `public/images` يدوياً مؤقتاً).

---

## الخطوة 11 — الدومين و SSL
hPanel → **دومينات**:
1. اربط الدومين الحقيقي بالموقع (لو لسه على `*.hostingersite.com`).
2. hPanel → **الحماية → SSL** → فعّل شهادة SSL مجانية + **إجبار HTTPS**.

---

## الخطوة 12 — إعدادات نهائية من لوحة التحكم
ادخل `https://YOUR_DOMAIN.com/admin` بحساب الأدمن، وحط:
- **الأرقام الحقيقية** (هاتف/واتساب) — إعدادات الموقع.
- **الصور الحقيقية** — الهيرو (الصفحة الرئيسية → سلايدر الهيرو) + «خدماتنا بالصور» + «مين إحنا».
- **الأسعار** الحقيقية — الباقات.
- **بيانات الشركة** (اسم/وصف/مواعيد/سوشيال).
- اكمل محتوى أي صفحة لسه فيها TODO.

---

## الخطوة 13 — التحقق النهائي
```bash
php artisan site:preflight
```
المفروض ميرجّعش مشاكل محتوى. (APP_DEBUG و canonical_host بيتظبطوا في `.env` زي الخطوة 6.)

ثم افتح المتصفح:
- `https://YOUR_DOMAIN.com` — الموقع شغّال على HTTPS.
- جرّب: الرئيسية، خدمة، منطقة، مقال، `/ac-calculator`، ولوحة `/admin`.
- اتأكد إن `favicon.ico` و`images/og-image.jpg` موجودين في `public`.

---

## مشاكل شائعة وحلولها
| المشكلة | السبب/الحل |
|---|---|
| صفحة بيضا / 500 | شغّل الموقع بـ `APP_DEBUG=true` مؤقتاً وشوف الخطأ، أو راجع `storage/logs/laravel.log`. رجّعها `false` بعدها. |
| `No application encryption key` | نسيت `php artisan key:generate`. |
| CSS/JS مش ظاهرين | `public/build` مش مرفوع، أو document root غلط (الخطوة 9). |
| خطأ اتصال قاعدة البيانات | راجع بيانات DB في `.env` (الاسم/المستخدم بالبادئة، Host=localhost). |
| الصور مش ظاهرة | لسه مترفعتش من `/admin`، أو `storage:link` مش متعمل. |
| التعديلات مش بتظهر | `php artisan config:clear && php artisan cache:clear && php artisan view:clear`. |
| 403 / Permission | صلاحيات المجلدات: `storage` و`bootstrap/cache` لازم يكونوا قابلين للكتابة (755/775). |

---

## ملاحظة: الرفع الآلي (اختياري)
لو حبيت لاحقاً الرفع يتعمل أوتوماتيك: فعّل موصّل Hostinger في Claude Code عبر `/mcp` (تسجيل دخول OAuth)، وبعدها جلسة جديدة تقدر تستخدم مهارة `deploy-to-hosting` بدل الخطوات اليدوية دي.
