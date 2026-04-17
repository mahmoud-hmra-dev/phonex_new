# Phonix -- قالب متجر إلكترونيات احترافي لمنصة Bagisto

قالب واجهة متجر حديث ثنائي اللغة (عربي RTL + إنجليزي LTR) مبني لمنصة Bagisto 2.3+. يتميز Phonix بنظام تصميم بألوان التيل، دعم الوضع الداكن، حركات GSAP التفاعلية، وتصميم متجاوب يعطي الأولوية للأجهزة المحمولة ومُحسّن لمتاجر الإلكترونيات.

## المميزات

- **دعم ثنائي اللغة** -- العربية (RTL) والإنجليزية (LTR) مع أكثر من 400 مفتاح ترجمة
- **الوضع الداكن** -- تبديل قائم على الفئات مع لوحة ألوان أسطح داكنة مخصصة
- **تصميم متجاوب** -- أولوية الأجهزة المحمولة مع خطوط متغيرة الحجم (`clamp()`)
- **نظام تصميم** -- لوحة ألوان تيل مخصصة، شبكة تباعد 8px، تسلسل ظلال هرمي
- **حركات تفاعلية** -- تأثيرات تمرير بواسطة GSAP وتأثيرات CSS الدقيقة
- **عارض شرائح** -- تكامل Swiper.js لعرض المنتجات والعلامات التجارية
- **تفاعلية Alpine.js** -- التركيز، مراقب التقاطع، الحالة المحفوظة
- **إمكانية الوصول** -- سمات ARIA، التنقل بلوحة المفاتيح، `prefers-reduced-motion`
- **بطاقات زجاجية** -- متغير بطاقة بتأثير الزجاج المصنفر مع ضبابية الخلفية
- **واجهة متجر كاملة** -- الرئيسية، قائمة المنتجات، تفاصيل المنتج، السلة، الدفع، الحساب، صفحات المصادقة

## لقطات الشاشة

لقطات الشاشة قريبا.

## المتطلبات

| المتطلب    | الإصدار |
|------------|---------|
| PHP        | 8.2+    |
| Bagisto    | 2.3+    |
| Node.js    | 18+     |
| Composer   | 2+      |

## التثبيت

### الخطوة 1: نسخ أو تثبيت الحزمة

ضع الحزمة في المسار `packages/Webkul/Phonix/` داخل مجلد Bagisto الرئيسي. ثم أضف مساحة الأسماء PSR-4 إلى ملف `composer.json` الرئيسي:

```json
"autoload": {
    "psr-4": {
        "Webkul\\Phonix\\": "packages/Webkul/Phonix/src/"
    }
}
```

### الخطوة 2: تسجيل مزود الخدمة

أضف المزود إلى ملف `bootstrap/providers.php`:

```php
return [
    // ... المزودون الحاليون
    Webkul\Phonix\Providers\PhonixServiceProvider::class,
];
```

### الخطوة 3: تسجيل القالب

أضف إدخال `phonix` إلى ملف `config/themes.php` تحت مفتاح `shop`:

```php
'phonix' => [
    'name'        => 'Phonix',
    'assets_path' => 'public/themes/shop/phonix',
    'views_path'  => 'packages/Webkul/Phonix/src/Resources/views',

    'vite' => [
        'hot_file'                 => 'shop-phonix-vite.hot',
        'build_directory'          => 'themes/shop/phonix/build',
        'package_assets_directory' => 'src/Resources/assets',
    ],
],
```

### الخطوة 4: تحديث التحميل التلقائي

```bash
composer dump-autoload
```

### الخطوة 5: تثبيت تبعيات الواجهة الأمامية

```bash
cd packages/Webkul/Phonix
npm install
```

### الخطوة 6: بناء الأصول

```bash
# التطوير (مع إعادة التحميل الفوري)
npm run dev

# الإنتاج
npm run build
```

تُخرج الأصول المبنية إلى `public/themes/shop/phonix/build/`.

### الخطوة 7: تفعيل القالب

عيّن قالب المتجر النشط في ملف `config/themes.php`:

```php
'shop-default' => 'phonix',
```

أو غيّره من لوحة إدارة Bagisto عبر **الإعدادات > القنوات > التصميم > القالب الافتراضي**.

### الخطوة 8: مسح ذاكرة التخزين المؤقت

```bash
php artisan optimize:clear
```

## الإعداد

### إعدادات القالب

يوجد ملف إعداد القالب في `packages/Webkul/Phonix/src/Config/themes.php` ويتم دمجه في `config('themes.shop.phonix')` عند التشغيل. لنشره وتخصيصه:

```bash
php artisan vendor:publish --tag=phonix-config
```

### تخصيص الألوان

عدّل ملف `packages/Webkul/Phonix/tailwind.config.js`. اللوحة الأساسية هي `phoenix`:

| الرمز          | Hex       | الاستخدام                |
|----------------|-----------|--------------------------|
| `phoenix-50`   | `#E6F7F9` | أفتح درجة، خلفية التمرير |
| `phoenix-100`  | `#C2ECF0` | خلفيات فاتحة            |
| `phoenix-200`  | `#94DCE4` | خلفية التحديد            |
| `phoenix-300`  | `#7DD8E0` | اللون الأساسي الداكن عند التمرير |
| `phoenix-400`  | `#4FC3D0` | اللون الأساسي في الوضع الداكن |
| `phoenix-500`  | `#1A8A96` | لون العلامة التجارية الأساسي |
| `phoenix-600`  | `#127883` | التمرير فوق الأساسي      |
| `phoenix-700`  | `#0F5F6B` | نشط / مضغوط             |
| `phoenix-800`  | `#0A4852` | تمييزات داكنة            |
| `phoenix-900`  | `#063138` | أغمق التمييزات           |
| `phoenix-950`  | `#031D20` | قريب من الأسود           |

ألوان التمييز:

| الرمز   | Hex       | الاستخدام           |
|---------|-----------|---------------------|
| `coral` | `#FF6B6B` | شارات الخصم، الأخطاء |
| `gold`  | `#FFD93D` | تقييم النجوم        |

### تخصيص الخطوط

يتم تحميل ثلاث عائلات خطوط من Google Fonts:

| الرمز      | عائلة الخط | الاستخدام                   |
|------------|------------|------------------------------|
| `cairo`    | Cairo      | النص العربي والعناوين        |
| `inter`    | Inter      | النص الإنجليزي               |
| `poppins`  | Poppins    | العناوين الإنجليزية          |

لتغيير الخطوط، عدّل كلا من `tailwind.config.js` (مفتاح `fontFamily`) وسطر `@import url(...)` في `src/Resources/assets/css/app.css`.

### الوضع الداكن

يستخدم الوضع الداكن استراتيجية `class` في Tailwind. أضف فئة `dark` إلى عنصر `<html>` لتفعيل الوضع الداكن. تتبدل خصائص CSS المخصصة تلقائيا:

| الخاصية             | فاتح        | داكن        |
|---------------------|-------------|-------------|
| `--color-bg`        | `#FFFFFF`   | `#0A1A1D`   |
| `--color-surface`   | `#F5F8F8`   | `#0F2528`   |
| `--color-card`      | `#FFFFFF`   | `#132E32`   |
| `--color-border`    | `#D4DCDD`   | `#1A3D42`   |
| `--color-text`      | `#1E2829`   | `#E8EDED`   |

### اللغة / RTL

يكتشف القالب `lang="ar"` أو `dir="rtl"` على عنصر `<html>` ويقوم تلقائيا بـ:

- تبديل خط النص إلى Cairo
- عكس التخطيط باستخدام خصائص CSS المنطقية (`start`/`end` بدلا من `left`/`right`)
- تدوير الأيقونات الاتجاهية (الأسهم) عبر `rtl:rotate-180`

ملفات الترجمة:
- `src/Resources/lang/en/app.php` (الإنجليزية)
- `src/Resources/lang/ar/app.php` (العربية)

## الصفحات والمسارات

جميع المسارات تبدأ بالبادئة `/phonix`.

| المسار                         | الاسم                        | العرض                         | الوصف                    |
|--------------------------------|------------------------------|-------------------------------|--------------------------|
| `GET /phonix`                  | `phonix.home`                | `phonix::home`                | الصفحة الرئيسية         |
| `GET /phonix/products`         | `phonix.products.index`      | `phonix::products.index`      | قائمة المنتجات           |
| `GET /phonix/products/{slug}`  | `phonix.products.view`       | `phonix::products.view`       | تفاصيل المنتج           |
| `GET /phonix/cart`             | `phonix.cart.index`          | `phonix::cart.index`          | سلة التسوق              |
| `GET /phonix/checkout`         | `phonix.checkout.index`      | `phonix::checkout.index`      | الدفع متعدد الخطوات     |
| `GET /phonix/checkout/success` | `phonix.checkout.success`    | `phonix::checkout.success`    | تأكيد الطلب             |
| `GET /phonix/account`          | `phonix.account.dashboard`   | `phonix::account.dashboard`   | لوحة تحكم الحساب        |
| `GET /phonix/account/orders`   | `phonix.account.orders`      | `phonix::account.orders.index`| سجل الطلبات             |
| `GET /phonix/account/orders/{id}` | `phonix.account.orders.view` | `phonix::account.orders.view` | تفاصيل الطلب         |
| `GET /phonix/account/addresses`| `phonix.account.addresses`   | `phonix::account.addresses.index` | دفتر العناوين      |
| `GET /phonix/account/wishlist` | `phonix.account.wishlist`    | `phonix::account.wishlist`    | قائمة الأمنيات          |
| `GET /phonix/account/profile`  | `phonix.account.profile`     | `phonix::account.profile`     | تعديل الملف الشخصي      |
| `GET /phonix/login`            | `phonix.auth.login`          | `phonix::auth.login`          | تسجيل الدخول            |
| `GET /phonix/register`         | `phonix.auth.register`       | `phonix::auth.register`       | التسجيل                 |
| `GET /phonix/forgot-password`  | `phonix.auth.forgot`         | `phonix::auth.forgot-password`| استعادة كلمة المرور     |

## المكونات

جميع المكونات تستخدم البادئة `x-phonix::`.

### المكونات الأساسية

| المكون | الاستخدام | الخصائص |
|--------|-----------|---------|
| `<x-phonix::button>` | `<x-phonix::button variant="primary" size="md">نص</x-phonix::button>` | `variant` (primary, outline, ghost)، `size` (sm, md, lg)، `type`، `href`، `disabled`، `loading` |
| `<x-phonix::card>` | `<x-phonix::card variant="glass">محتوى</x-phonix::card>` | `variant` (default, glass) |
| `<x-phonix::badge>` | `<x-phonix::badge type="sale">-20%</x-phonix::badge>` | `type` (new, sale, hot) |
| `<x-phonix::input>` | `<x-phonix::input type="email" label="البريد" name="email" />` | `type`، `label`، `error`، `name` |
| `<x-phonix::modal>` | `<x-phonix::modal name="quick-view" maxWidth="xl">محتوى</x-phonix::modal>` | `name` (مطلوب)، `maxWidth` (sm, md, lg, xl, 2xl) |
| `<x-phonix::breadcrumb>` | `<x-phonix::breadcrumb :items="$items" />` | `items` (مصفوفة `['label' => '', 'url' => '']`) |
| `<x-phonix::pagination>` | `<x-phonix::pagination :currentPage="1" :totalPages="7" />` | `currentPage`، `totalPages`، `from`، `to`، `total` |
| `<x-phonix::section-heading>` | `<x-phonix::section-heading title="مميز" subtitle="أفضل الاختيارات" />` | `title`، `subtitle`، `viewAllUrl` |

### مكونات المحتوى

| المكون | الوصف |
|--------|-------|
| `<x-phonix::hero>` | قسم البطل كامل العرض مع خلفية متدرجة وأشكال زخرفية |
| `<x-phonix::product-card>` | بطاقة منتج مع صورة وشارات وتقييم وسعر وإجراءات سريعة |
| `<x-phonix::product-gallery>` | معرض صور المنتج مع صور مصغرة |
| `<x-phonix::product-tabs>` | محتوى مبوب لتفاصيل المنتج (الوصف، المواصفات، المراجعات) |
| `<x-phonix::categories-grid>` | شبكة الفئات مع أيقونات |
| `<x-phonix::featured-products>` | عرض المنتجات المميزة |
| `<x-phonix::flash-deals>` | بطاقات العروض السريعة |
| `<x-phonix::deal-of-day>` | قسم صفقة اليوم المميزة |
| `<x-phonix::brands-carousel>` | عرض شعارات العلامات التجارية (Swiper) |
| `<x-phonix::testimonials>` | قسم آراء العملاء |
| `<x-phonix::stats-counter>` | عدادات إحصائيات متحركة |
| `<x-phonix::features-bar>` | شريط ميزات الثقة (الشحن، الإرجاع، إلخ) |
| `<x-phonix::newsletter>` | نموذج الاشتراك في النشرة الإخبارية |
| `<x-phonix::filters-sidebar>` | لوحة تصفية قائمة المنتجات |
| `<x-phonix::cart-drawer>` | درج السلة المنزلق |

### مكونات التخطيط

| المكون | الوصف |
|--------|-------|
| `<x-phonix::layouts.index>` | التخطيط الرئيسي (يحتوي الرأس + التذييل + أصول Vite) |
| `<x-phonix::layouts.header.index>` | رأس الموقع مع التنقل والبحث |
| `<x-phonix::layouts.header.mobile-nav>` | درج التنقل للأجهزة المحمولة |
| `<x-phonix::layouts.footer.index>` | تذييل الموقع مع الروابط وحقوق النشر |
| `<x-phonix::account.layout>` | تخطيط الشريط الجانبي لصفحات الحساب |

## استكشاف الأخطاء وإصلاحها

| المشكلة | الحل |
|---------|------|
| فشل البناء | تحقق من إصدار Node.js 18+. احذف `node_modules` وأعد تشغيل `npm install`. |
| القالب لا يظهر | تأكد من وجود `PhonixServiceProvider` في `bootstrap/providers.php` وإدخال `phonix` في `config/themes.php`. |
| الأنماط مفقودة | شغّل `npm run build` من `packages/Webkul/Phonix/`. تحقق من وجود ملفات الإخراج في `public/themes/shop/phonix/build/`. |
| RTL لا يعمل | تأكد من أن عنصر `<html>` يحتوي على `lang="ar"` أو `dir="rtl"`. |
| الوضع الداكن لا يعمل | تحقق من `darkMode: "class"` في `tailwind.config.js` وأن فئة `dark` مفعّلة على `<html>`. |
| HMR لا يتصل | تحقق من متغيرات البيئة `VITE_HOST` و `VITE_PORT`. المنفذ الافتراضي هو `5174`. |
| مشاكل التخزين المؤقت | شغّل `php artisan optimize:clear` بعد أي تغيير في الإعداد أو المزودين. |

## المساهمة

1. انسخ المستودع (Fork)
2. أنشئ فرع ميزة (`git checkout -b feature/your-feature`)
3. اتبع نمط الكود الحالي -- استخدم Laravel Pint لـ PHP و Prettier لـ JS/CSS
4. أضف ترجمات لكل من اللغتين `en` و `ar`
5. اختبر في وضعي LTR و RTL، وفي القالب الفاتح والداكن
6. قدّم طلب سحب (Pull Request)

## الرخصة

MIT

## الإسهامات

- [Bagisto](https://bagisto.com) بواسطة Webkul
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)
- [GSAP](https://greensock.com/gsap/) بواسطة GreenSock
- [Swiper.js](https://swiperjs.com)
- [Google Fonts](https://fonts.google.com) -- Cairo, Inter, Poppins
