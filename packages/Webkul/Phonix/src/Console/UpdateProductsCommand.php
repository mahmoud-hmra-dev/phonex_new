<?php

declare(strict_types=1);

namespace Webkul\Phonix\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds Arabic translations + real product images to all Phonix-seeded products.
 *
 * Usage:  php artisan phonix:update-products
 */
class UpdateProductsCommand extends Command
{
    protected $signature   = 'phonix:update-products {--images-only} {--translations-only}';
    protected $description = 'Add Arabic translations and download real product images for all Phonix products.';

    // -------------------------------------------------------------------------
    // Attribute IDs (Bagisto defaults)
    // -------------------------------------------------------------------------
    private const ATTR_NAME              = 2;
    private const ATTR_URL_KEY           = 3;
    private const ATTR_SHORT_DESCRIPTION = 9;
    private const ATTR_DESCRIPTION       = 10;

    public function handle(): int
    {
        $onlyImages       = (bool) $this->option('images-only');
        $onlyTranslations = (bool) $this->option('translations-only');

        $products = DB::table('products')->get();

        $this->info("Found {$products->count()} products.");

        $translations = $this->getArabicTranslations();
        $imageMap     = $this->getImageUrls();

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $translatedCount = 0;
        $imagesUpdated   = 0;

        foreach ($products as $product) {
            $englishName = DB::table('product_attribute_values')
                ->where('product_id', $product->id)
                ->where('attribute_id', self::ATTR_NAME)
                ->where('locale', 'en')
                ->value('text_value');

            if (! $englishName) {
                $bar->advance();
                continue;
            }

            // ------------------------------------------------------------------
            // Arabic translations
            // ------------------------------------------------------------------
            if (! $onlyImages) {
                $arData = $this->resolveArabicData($englishName, $translations);

                if ($arData) {
                    $this->upsertLocaleAttribute($product->id, self::ATTR_NAME, 'en', null, $arData['name'], 'text_value');
                    // Arabic locale
                    $this->upsertLocaleAttribute($product->id, self::ATTR_NAME, 'ar', null, $arData['name_ar'], 'text_value');
                    $this->upsertLocaleAttribute($product->id, self::ATTR_SHORT_DESCRIPTION, 'ar', null, $arData['short_ar'], 'text_value');
                    $this->upsertLocaleAttribute($product->id, self::ATTR_DESCRIPTION, 'ar', null, $arData['desc_ar'], 'text_value');
                    $translatedCount++;
                }
            }

            // ------------------------------------------------------------------
            // Product images
            // ------------------------------------------------------------------
            if (! $onlyTranslations) {
                $imageUrl = $this->resolveImageUrl($englishName, $imageMap);

                if ($imageUrl) {
                    $downloaded = $this->downloadAndSaveImage($product->id, $imageUrl);
                    if ($downloaded) {
                        $imagesUpdated++;
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (! $onlyImages) {
            $this->info("Arabic translations added/updated: {$translatedCount}");
        }

        if (! $onlyTranslations) {
            $this->info("Product images updated: {$imagesUpdated}");
        }

        // Rebuild product flat index
        $this->info('Rebuilding product flat index…');
        try {
            $this->reindexFlat();
            $this->info('Done.');
        } catch (\Throwable $e) {
            $this->warn('Flat index rebuild failed: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Upsert a locale-specific product attribute value.
     */
    private function upsertLocaleAttribute(
        int $productId,
        int $attributeId,
        string $locale,
        ?string $channel,
        mixed $value,
        string $valueColumn = 'text_value'
    ): void {
        $uniqueId = implode('|', [$channel ?? '', $locale, $productId, $attributeId]);

        $exists = DB::table('product_attribute_values')
            ->where('unique_id', $uniqueId)
            ->exists();

        $data = [
            'text_value'     => null,
            'boolean_value'  => null,
            'integer_value'  => null,
            'float_value'    => null,
            'datetime_value' => null,
            'date_value'     => null,
            'json_value'     => null,
            $valueColumn     => $value,
        ];

        if ($exists) {
            DB::table('product_attribute_values')
                ->where('unique_id', $uniqueId)
                ->update([$valueColumn => $value]);
        } else {
            DB::table('product_attribute_values')->insert(array_merge($data, [
                'attribute_id' => $attributeId,
                'product_id'   => $productId,
                'channel'      => $channel,
                'locale'       => $locale,
                'unique_id'    => $uniqueId,
            ]));
        }
    }

    /**
     * Download an image from URL and save to product storage.
     * Replaces the first existing image or inserts new one.
     */
    private function downloadAndSaveImage(int $productId, string $url): bool
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'    => 20,
                    'user_agent' => 'Mozilla/5.0 (compatible; Phonix/1.0)',
                    'follow_location' => 1,
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $content = @file_get_contents($url, false, $context);

            if ($content === false || strlen($content) < 500) {
                return false;
            }

            // Detect extension from URL or content
            $ext = 'jpg';
            if (str_contains($url, '.png') || str_starts_with($content, "\x89PNG")) {
                $ext = 'png';
            } elseif (str_contains($url, '.webp')) {
                $ext = 'webp';
            }

            $filename  = Str::random(20) . '.' . $ext;
            $imagePath = 'product/' . $productId . '/' . $filename;

            Storage::disk('public')->put($imagePath, $content);

            // Remove old images for this product
            DB::table('product_images')
                ->where('product_id', $productId)
                ->delete();

            DB::table('product_images')->insert([
                'type'       => 'images',
                'path'       => $imagePath,
                'product_id' => $productId,
                'position'   => 1,
            ]);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Find Arabic data for a given English product name using pattern matching.
     */
    private function resolveArabicData(string $englishName, array $translations): ?array
    {
        // Exact match first
        if (isset($translations[$englishName])) {
            return $translations[$englishName];
        }

        // Partial match
        foreach ($translations as $key => $data) {
            if (str_contains($englishName, $key) || str_contains($key, $englishName)) {
                return $data;
            }
        }

        // Auto-generate for unknown products
        return $this->autoTranslate($englishName);
    }

    /**
     * Find image URL for a product by matching its English name.
     */
    private function resolveImageUrl(string $englishName, array $imageMap): ?string
    {
        $nameLower = strtolower($englishName);

        foreach ($imageMap as $pattern => $url) {
            if (str_contains($nameLower, strtolower($pattern))) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Auto-generate Arabic data for unmatched products.
     */
    private function autoTranslate(string $englishName): array
    {
        $translations = [
            'Samsung' => 'سامسونج', 'Apple' => 'آبل', 'Xiaomi' => 'شاومي',
            'Tecno' => 'تيكنو', 'Sony' => 'سوني', 'JBL' => 'JBL',
            'Galaxy' => 'جالاكسي', 'iPhone' => 'آيفون', 'iPad' => 'آيباد',
            'Redmi' => 'ريدمي', 'Spark' => 'سبارك', 'Pova' => 'بوفا',
            'Camon' => 'كامون', 'AirPods' => 'إيربودز', 'AirTag' => 'إيرتاج',
            'PlayStation' => 'بلايستيشن', 'PS5' => 'بلايستيشن 5',
            'Buds' => 'سماعات', 'Watch' => 'ساعة', 'Pencil' => 'قلم',
            'Tab' => 'تابلت', 'Pro' => 'برو', 'Max' => 'ماكس', 'Ultra' => 'الترا',
            'Air' => 'إير', 'Plus' => 'بلس', 'Note' => 'نوت',
        ];

        $nameAr = $englishName;
        foreach ($translations as $en => $ar) {
            $nameAr = str_replace($en, $ar, $nameAr);
        }

        // Translate storage specs
        $nameAr = preg_replace('/(\d+)TB/', '$1 تيرابايت', $nameAr);
        $nameAr = preg_replace('/(\d+)GB/', '$1 جيجابايت', $nameAr);
        $nameAr = preg_replace('/(\d+)\/(\d+) جيجابايت/', 'ذاكرة $1 جيجا / تخزين $2 جيجا', $nameAr);

        return [
            'name'     => $englishName,
            'name_ar'  => $nameAr,
            'short_ar' => "متوفر الآن في متجر فونيكس. {$nameAr} - جودة مضمونة وأسعار منافسة.",
            'desc_ar'  => "<p>{$nameAr} متوفر في متجر فونيكس للإلكترونيات. جودة عالية وأداء متميز. اطلب الآن واستمتع بالتوصيل السريع.</p>",
        ];
    }

    /**
     * Rebuild product_flat table.
     */
    private function reindexFlat(): void
    {
        if (! class_exists(\Webkul\Product\Helpers\Indexers\Flat::class)) {
            return;
        }

        $indexer  = app(\Webkul\Product\Helpers\Indexers\Flat::class);
        $products = \Webkul\Product\Models\Product::all();

        foreach ($products->chunk(50) as $chunk) {
            $indexer->reindexBatch($chunk);
        }

        // Also reindex inventory & price
        if (class_exists(\Webkul\Product\Helpers\Indexers\Inventory::class)) {
            app(\Webkul\Product\Helpers\Indexers\Inventory::class)->reindexFull();
        }
        if (class_exists(\Webkul\Product\Helpers\Indexers\Price::class)) {
            app(\Webkul\Product\Helpers\Indexers\Price::class)->reindexFull();
        }
    }

    // =========================================================================
    // Arabic Translation Map
    // =========================================================================

    private function getArabicTranslations(): array
    {
        return [
            // -----------------------------------------------------------------
            // Samsung Phones
            // -----------------------------------------------------------------
            'Samsung Galaxy A06 5G 4/128GB' => [
                'name'     => 'Samsung Galaxy A06 5G 4/128GB',
                'name_ar'  => 'سامسونج جالاكسي A06 5G - ذاكرة 4 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A06 بتقنية 5G وذاكرة عشوائية 4 جيجا وتخزين 128 جيجابايت. أداء موثوق وبطارية تدوم طويلاً.',
                'desc_ar'  => '<p>سامسونج جالاكسي A06 5G يجمع بين تقنية الجيل الخامس وجودة سامسونج الموثوقة. يتميز بشاشة واضحة ومعالج قوي وبطارية تدوم طويلاً، مما يجعله الخيار المثالي للاستخدام اليومي.</p>',
            ],
            'Samsung Galaxy A07 4G 4/64GB' => [
                'name'     => 'Samsung Galaxy A07 4G 4/64GB',
                'name_ar'  => 'سامسونج جالاكسي A07 4G - ذاكرة 4 جيجا / تخزين 64 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A07 بشبكة 4G وذاكرة 4 جيجا وتخزين 64 جيجابايت. قيمة رائعة مع جودة سامسونج.',
                'desc_ar'  => '<p>سامسونج جالاكسي A07 يوفر تجربة استخدام ممتازة بسعر مناسب. مزود بشاشة HD+ واسعة ومعالج موثوق وكاميرا ثلاثية تلتقط لحظاتك بوضوح.</p>',
            ],
            'Samsung Galaxy A07 4G 4/128GB' => [
                'name'     => 'Samsung Galaxy A07 4G 4/128GB',
                'name_ar'  => 'سامسونج جالاكسي A07 4G - ذاكرة 4 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A07 بشبكة 4G وذاكرة 4 جيجا وتخزين 128 جيجابايت. مساحة أكبر للتطبيقات والصور.',
                'desc_ar'  => '<p>سامسونج جالاكسي A07 بتخزين 128 جيجابايت يمنحك مساحة كافية لتطبيقاتك وصورك. يتميز بشاشة HD+ واسعة ومعالج قوي وبطارية 5000 مللي أمبير.</p>',
            ],
            'Samsung Galaxy A16 8/256GB' => [
                'name'     => 'Samsung Galaxy A16 8/256GB',
                'name_ar'  => 'سامسونج جالاكسي A16 - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A16 بذاكرة 8 جيجا وتخزين 256 جيجابايت. أداء ممتاز للمهام المتعددة.',
                'desc_ar'  => '<p>سامسونج جالاكسي A16 يقدم أداءً متميزاً مع ذاكرة 8 جيجا وتخزين 256 جيجابايت. يتميز بشاشة Super AMOLED مذهلة وكاميرا احترافية ومعالج Exynos قوي.</p>',
            ],
            'Samsung Galaxy A17 4/128GB' => [
                'name'     => 'Samsung Galaxy A17 4/128GB',
                'name_ar'  => 'سامسونج جالاكسي A17 - ذاكرة 4 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A17 بذاكرة 4 جيجا وتخزين 128 جيجابايت. تصميم أنيق وأداء موثوق.',
                'desc_ar'  => '<p>سامسونج جالاكسي A17 يوفر تجربة استخدام احترافية بتصميم أنيق رفيع. يتضمن شاشة AMOLED FHD+ ومعالجاً قوياً وكاميرا متعددة العدسات.</p>',
            ],
            'Samsung Galaxy A17 6/128GB' => [
                'name'     => 'Samsung Galaxy A17 6/128GB',
                'name_ar'  => 'سامسونج جالاكسي A17 - ذاكرة 6 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A17 بذاكرة 6 جيجا وتخزين 128 جيجابايت. تعدد مهام أسرع وأداء محسّن.',
                'desc_ar'  => '<p>سامسونج جالاكسي A17 بذاكرة 6 جيجا يتيح لك تشغيل تطبيقات متعددة بسلاسة. يتميز بشاشة AMOLED مذهلة ونظام كاميرا متطور وبطارية قوية.</p>',
            ],
            'Samsung Galaxy A17 8/256GB' => [
                'name'     => 'Samsung Galaxy A17 8/256GB',
                'name_ar'  => 'سامسونج جالاكسي A17 - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A17 النسخة المتميزة بذاكرة 8 جيجا وتخزين 256 جيجابايت.',
                'desc_ar'  => '<p>سامسونج جالاكسي A17 النسخة المتميزة تجمع بين أداء استثنائي وتخزين واسع. مثالي للمصورين ومحبي الألعاب والمستخدمين المحترفين.</p>',
            ],
            'Samsung Galaxy A26 6/128GB' => [
                'name'     => 'Samsung Galaxy A26 6/128GB',
                'name_ar'  => 'سامسونج جالاكسي A26 - ذاكرة 6 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A26 بذاكرة 6 جيجا وتخزين 128 جيجابايت. تصميم عصري وأداء متقدم.',
                'desc_ar'  => '<p>سامسونج جالاكسي A26 يأتي بتصميم عصري أنيق وأداء متقدم. يضم شاشة Super AMOLED FHD+ ومعالج قوي وكاميرا 50 ميجابكسل.</p>',
            ],
            'Samsung Galaxy A26 8/256GB' => [
                'name'     => 'Samsung Galaxy A26 8/256GB',
                'name_ar'  => 'سامسونج جالاكسي A26 - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A26 النسخة المتميزة بذاكرة 8 جيجا وتخزين 256 جيجابايت.',
                'desc_ar'  => '<p>سامسونج جالاكسي A26 بذاكرة 8 جيجا وتخزين 256 جيجابايت يقدم تجربة استخدام لا مثيل لها. شاشة AMOLED مذهلة وكاميرا احترافية وأداء خارق.</p>',
            ],
            'Samsung Galaxy A36 8/256GB' => [
                'name'     => 'Samsung Galaxy A36 8/256GB',
                'name_ar'  => 'سامسونج جالاكسي A36 - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A36 الجديد بذاكرة 8 جيجا وتخزين 256 جيجابايت. أحدث تقنيات سامسونج.',
                'desc_ar'  => '<p>سامسونج جالاكسي A36 يمثل الجيل الجديد من الهواتف المتوسطة. يتميز بشاشة Super AMOLED FHD+ ومعالج Snapdragon قوي وكاميرا 200 ميجابكسل.</p>',
            ],
            'Samsung Galaxy A56 8/128GB' => [
                'name'     => 'Samsung Galaxy A56 8/128GB',
                'name_ar'  => 'سامسونج جالاكسي A56 - ذاكرة 8 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A56 الفلاجشيب المتوسط بذاكرة 8 جيجا وتخزين 128 جيجابايت.',
                'desc_ar'  => '<p>سامسونج جالاكسي A56 يقدم مواصفات الفئة العليا بسعر مناسب. يتميز بشاشة Dynamic AMOLED ومعالج متطور وكاميرا احترافية بثلاث عدسات.</p>',
            ],
            'Samsung Galaxy A56 8/256GB' => [
                'name'     => 'Samsung Galaxy A56 8/256GB',
                'name_ar'  => 'سامسونج جالاكسي A56 - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف سامسونج جالاكسي A56 النسخة المتميزة بذاكرة 8 جيجا وتخزين 256 جيجابايت.',
                'desc_ar'  => '<p>سامسونج جالاكسي A56 بتخزين 256 جيجابايت الخيار الأمثل لمن يريد الأفضل. شاشة AMOLED مذهلة وكاميرا بدقة عالية وأداء فائق للمحترفين.</p>',
            ],

            // -----------------------------------------------------------------
            // Tecno Phones
            // -----------------------------------------------------------------
            'Tecno Spark Go One 3/64GB' => [
                'name'     => 'Tecno Spark Go One 3/64GB',
                'name_ar'  => 'تيكنو سبارك جو ون - ذاكرة 3 جيجا / تخزين 64 جيجا',
                'short_ar' => 'هاتف تيكنو سبارك جو ون بذاكرة 3 جيجا وتخزين 64 جيجابايت. قيمة لا تُقاوم لميزانية محدودة.',
                'desc_ar'  => '<p>تيكنو سبارك جو ون هو الرفيق المثالي للمستخدمين الباحثين عن قيمة عالية بسعر مناسب. يتميز بشاشة واسعة وبطارية تدوم طويلاً وأداء موثوق للمهام اليومية.</p>',
            ],
            'Tecno Spark Go One 4/128GB' => [
                'name'     => 'Tecno Spark Go One 4/128GB',
                'name_ar'  => 'تيكنو سبارك جو ون - ذاكرة 4 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف تيكنو سبارك جو ون بذاكرة 4 جيجا وتخزين 128 جيجابايت. أداء أفضل ومساحة أوسع.',
                'desc_ar'  => '<p>تيكنو سبارك جو ون بذاكرة 4 جيجا وتخزين 128 جيجابايت يوفر تجربة استخدام أكثر سلاسة. مثالي للمستخدمين المبتدئين والباحثين عن هاتف موثوق بسعر معقول.</p>',
            ],
            'Tecno Spark Go 2 3/64GB' => [
                'name'     => 'Tecno Spark Go 2 3/64GB',
                'name_ar'  => 'تيكنو سبارك جو 2 - ذاكرة 3 جيجا / تخزين 64 جيجا',
                'short_ar' => 'هاتف تيكنو سبارك جو 2 بذاكرة 3 جيجا وتخزين 64 جيجابايت. هاتف اقتصادي بأداء جيد.',
                'desc_ar'  => '<p>تيكنو سبارك جو 2 يقدم تجربة هاتف ذكي كاملة بسعر في متناول الجميع. شاشة واضحة وكاميرا مقبولة وبطارية تدوم طوال اليوم.</p>',
            ],
            'Tecno Spark Go 3 4/64GB' => [
                'name'     => 'Tecno Spark Go 3 4/64GB',
                'name_ar'  => 'تيكنو سبارك جو 3 - ذاكرة 4 جيجا / تخزين 64 جيجا',
                'short_ar' => 'هاتف تيكنو سبارك جو 3 بذاكرة 4 جيجا وتخزين 64 جيجابايت. الجيل الجديد بأداء محسّن.',
                'desc_ar'  => '<p>تيكنو سبارك جو 3 يأتي بتحسينات ملموسة على سابقه. يتميز بمعالج أسرع وكاميرا أوضح وبطارية 5000 مللي أمبير تضمن استمرارية الاستخدام.</p>',
            ],
            'Tecno Spark 4C 8/256GB' => [
                'name'     => 'Tecno Spark 4C 8/256GB',
                'name_ar'  => 'تيكنو سبارك 4C - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف تيكنو سبارك 4C بذاكرة 8 جيجا وتخزين 256 جيجابايت. مواصفات متميزة بسعر تنافسي.',
                'desc_ar'  => '<p>تيكنو سبارك 4C يرفع مستوى هواتف تيكنو بذاكرة 8 جيجا وتخزين ضخم 256 جيجابايت. أداء ممتاز للألعاب والتطبيقات المتطلبة بسعر في المتناول.</p>',
            ],
            'Tecno Spark 20 Pro 12/256GB' => [
                'name'     => 'Tecno Spark 20 Pro 12/256GB',
                'name_ar'  => 'تيكنو سبارك 20 برو - ذاكرة 12 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف تيكنو سبارك 20 برو بذاكرة 12 جيجا وتخزين 256 جيجابايت. الفلاجشيب الاقتصادي من تيكنو.',
                'desc_ar'  => '<p>تيكنو سبارك 20 برو يقدم مواصفات الفئة العليا بسعر مناسب. شاشة AMOLED مذهلة وذاكرة 12 جيجا وكاميرا 108 ميجابكسل لتصوير احترافي.</p>',
            ],
            'Tecno Spark Slim 8/256GB' => [
                'name'     => 'Tecno Spark Slim 8/256GB',
                'name_ar'  => 'تيكنو سبارك سليم - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف تيكنو سبارك سليم بذاكرة 8 جيجا وتخزين 256 جيجابايت. تصميم نحيف أنيق مع أداء قوي.',
                'desc_ar'  => '<p>تيكنو سبارك سليم يجمع بين التصميم النحيف الأنيق والأداء القوي. أحد أرفع الهواتف في فئته مع شاشة AMOLED ومعالج متطور.</p>',
            ],
            'Tecno Pova 7 8/256GB' => [
                'name'     => 'Tecno Pova 7 8/256GB',
                'name_ar'  => 'تيكنو بوفا 7 - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف تيكنو بوفا 7 بذاكرة 8 جيجا وتخزين 256 جيجابايت. بطارية عملاقة للألعاب والترفيه.',
                'desc_ar'  => '<p>تيكنو بوفا 7 مصمم للاعبين ومحبي الترفيه. يتميز ببطارية 6000 مللي أمبير هائلة وشاشة عالية معدل التحديث وأداء قوي لساعات لعب لا تنتهي.</p>',
            ],
            'Tecno Pova 7 5G 8/256GB' => [
                'name'     => 'Tecno Pova 7 5G 8/256GB',
                'name_ar'  => 'تيكنو بوفا 7 5G - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف تيكنو بوفا 7 بتقنية 5G وذاكرة 8 جيجا وتخزين 256 جيجابايت. سرعة الجيل الخامس مع بطارية ضخمة.',
                'desc_ar'  => '<p>تيكنو بوفا 7 5G يجمع بين سرعة تقنية 5G وبطارية 6000 مللي أمبير الهائلة. مثالي للألعاب المتصلة بالإنترنت والبث المباشر عالي الدقة.</p>',
            ],
            'Tecno Camon 20 Premier 8/512GB' => [
                'name'     => 'Tecno Camon 20 Premier 8/512GB',
                'name_ar'  => 'تيكنو كامون 20 بريمير - ذاكرة 8 جيجا / تخزين 512 جيجا',
                'short_ar' => 'هاتف تيكنو كامون 20 بريمير بذاكرة 8 جيجا وتخزين 512 جيجابايت. كاميرا احترافية ومساحة تخزين ضخمة.',
                'desc_ar'  => '<p>تيكنو كامون 20 بريمير هو قمة سلسلة كامون. يتميز بكاميرا 108 ميجابكسل وتخزين 512 جيجابايت وشاشة AMOLED FHD+ للتصوير الاحترافي.</p>',
            ],
            'Tecno Camon 40 Pro 5G 12/256GB' => [
                'name'     => 'Tecno Camon 40 Pro 5G 12/256GB',
                'name_ar'  => 'تيكنو كامون 40 برو 5G - ذاكرة 12 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف تيكنو كامون 40 برو 5G بذاكرة 12 جيجا وتخزين 256 جيجابايت. أحدث فلاجشيب من تيكنو.',
                'desc_ar'  => '<p>تيكنو كامون 40 برو 5G هو الفلاجشيب الجديد من تيكنو. يتميز بتقنية 5G وذاكرة 12 جيجا وكاميرا الذكاء الاصطناعي المتطورة وشاشة AMOLED مذهلة.</p>',
            ],

            // -----------------------------------------------------------------
            // Apple iPhones
            // -----------------------------------------------------------------
            'Apple iPhone 15 Pro (SIM) 256GB' => [
                'name'     => 'Apple iPhone 15 Pro (SIM) 256GB',
                'name_ar'  => 'آبل آيفون 15 برو (SIM) - 256 جيجابايت',
                'short_ar' => 'آيفون 15 برو بشريحة SIM وتخزين 256 جيجابايت. قوة شريحة A17 Pro وكاميرا تيتانيوم احترافية.',
                'desc_ar'  => '<p>آبل آيفون 15 برو يقدم أداءً استثنائياً بشريحة A17 Pro التيتانيوم وكاميرا احترافية 48 ميجابكسل. نظام تيتانيوم خفيف ومتين مع Dynamic Island للإشعارات الذكية.</p>',
            ],
            'Apple iPhone 16 (SIM) 128GB' => [
                'name'     => 'Apple iPhone 16 (SIM) 128GB',
                'name_ar'  => 'آبل آيفون 16 (SIM) - 128 جيجابايت',
                'short_ar' => 'آيفون 16 بشريحة SIM وتخزين 128 جيجابايت. شريحة A18 والذكاء الاصطناعي من آبل.',
                'desc_ar'  => '<p>آبل آيفون 16 يأتي بشريحة A18 القوية ودعم كامل لميزات الذكاء الاصطناعي Apple Intelligence. يتميز بزر الكاميرا الجديد وكاميرا 48 ميجابكسل محسّنة.</p>',
            ],
            'Apple iPhone 17 (eSIM) 256GB' => [
                'name'     => 'Apple iPhone 17 (eSIM) 256GB',
                'name_ar'  => 'آبل آيفون 17 (eSIM) - 256 جيجابايت',
                'short_ar' => 'آيفون 17 بشريحة eSIM وتخزين 256 جيجابايت. أحدث آيفون من آبل بشريحة A19.',
                'desc_ar'  => '<p>آبل آيفون 17 هو أحدث إصدارات آيفون. يضم شريحة A19 بيونيك الأقوى، شاشة LTPO OLED فائقة الدقة، ونظام كاميرا متطور لتصوير سينمائي.</p>',
            ],
            'Apple iPhone 17 (SIM) 256GB' => [
                'name'     => 'Apple iPhone 17 (SIM) 256GB',
                'name_ar'  => 'آبل آيفون 17 (SIM) - 256 جيجابايت',
                'short_ar' => 'آيفون 17 بشريحة SIM فيزيائية وتخزين 256 جيجابايت. آخر ابتكارات آبل بين يديك.',
                'desc_ar'  => '<p>آبل آيفون 17 بشريحة SIM يقدم نفس التجربة الاستثنائية بخيار الشريحة الفيزيائية. شريحة A19 بيونيك وكاميرا احترافية محسّنة مع Apple Intelligence.</p>',
            ],
            'Apple iPhone 17 Air (eSIM) 256GB' => [
                'name'     => 'Apple iPhone 17 Air (eSIM) 256GB',
                'name_ar'  => 'آبل آيفون 17 إير (eSIM) - 256 جيجابايت',
                'short_ar' => 'آيفون 17 إير بشريحة eSIM وتخزين 256 جيجابايت. أرفع آيفون على الإطلاق.',
                'desc_ar'  => '<p>آبل آيفون 17 إير هو الأرفع في تاريخ آيفون بسماكة مذهلة. يجمع بين التصميم الخارق الرفاهية وأداء شريحة A19 بيونيك في قالب لا مثيل له خفة ورشاقة.</p>',
            ],
            'Apple iPhone 17 Air (eSIM) 512GB' => [
                'name'     => 'Apple iPhone 17 Air (eSIM) 512GB',
                'name_ar'  => 'آبل آيفون 17 إير (eSIM) - 512 جيجابايت',
                'short_ar' => 'آيفون 17 إير بشريحة eSIM وتخزين 512 جيجابايت. رفاهية لا حدود لها مع تخزين ضخم.',
                'desc_ar'  => '<p>آبل آيفون 17 إير بتخزين 512 جيجابايت يمنحك مساحة لا حدود لها. التصميم الأرفع في العالم مع أداء استثنائي ومئات الأفلام والألبومات والتطبيقات.</p>',
            ],
            'Apple iPhone 17 Pro (eSIM) 256GB' => [
                'name'     => 'Apple iPhone 17 Pro (eSIM) 256GB',
                'name_ar'  => 'آبل آيفون 17 برو (eSIM) - 256 جيجابايت',
                'short_ar' => 'آيفون 17 برو بشريحة eSIM وتخزين 256 جيجابايت. قمة الأداء الاحترافي من آبل.',
                'desc_ar'  => '<p>آبل آيفون 17 برو يمثل قمة التكنولوجيا. يضم شريحة A19 Pro بيونيك ونظام كاميرا احترافي ثلاثي بعدسة 48 ميجابكسل وتصميم تيتانيوم فاخر.</p>',
            ],
            'Apple iPhone 17 Pro (eSIM) 512GB' => [
                'name'     => 'Apple iPhone 17 Pro (eSIM) 512GB',
                'name_ar'  => 'آبل آيفون 17 برو (eSIM) - 512 جيجابايت',
                'short_ar' => 'آيفون 17 برو بشريحة eSIM وتخزين 512 جيجابايت. للمحترفين الذين لا يقبلون المساومة.',
                'desc_ar'  => '<p>آبل آيفون 17 برو بتخزين 512 جيجابايت للمحترفين والمبدعين. سجّل مقاطع الفيديو 8K، التقط صوراً RAW احترافية، واستمتع بأداء لا مثيل له.</p>',
            ],
            'Apple iPhone 17 Pro Max (eSIM) 256GB' => [
                'name'     => 'Apple iPhone 17 Pro Max (eSIM) 256GB',
                'name_ar'  => 'آبل آيفون 17 برو ماكس (eSIM) - 256 جيجابايت',
                'short_ar' => 'آيفون 17 برو ماكس بشريحة eSIM وتخزين 256 جيجابايت. الأكبر والأقوى في عائلة آيفون.',
                'desc_ar'  => '<p>آبل آيفون 17 برو ماكس هو التاج في عائلة آيفون. شاشة Super Retina XDR الأكبر والأشد سطوعاً مع شريحة A19 Pro وأفضل نظام كاميرا آيفون على الإطلاق.</p>',
            ],
            'Apple iPhone 17 Pro Max (eSIM) 512GB' => [
                'name'     => 'Apple iPhone 17 Pro Max (eSIM) 512GB',
                'name_ar'  => 'آبل آيفون 17 برو ماكس (eSIM) - 512 جيجابايت',
                'short_ar' => 'آيفون 17 برو ماكس بشريحة eSIM وتخزين 512 جيجابايت. الفائق بين الفائقين.',
                'desc_ar'  => '<p>آبل آيفون 17 برو ماكس بتخزين 512 جيجابايت يأتي بكل شيء في أعلى مستوياته. الشاشة الأكبر والأجمل مع أداء استثنائي وكاميرا تعيد تعريف التصوير الاحترافي.</p>',
            ],
            'Apple iPhone 17 Pro Max (eSIM) 1TB' => [
                'name'     => 'Apple iPhone 17 Pro Max (eSIM) 1TB',
                'name_ar'  => 'آبل آيفون 17 برو ماكس (eSIM) - 1 تيرابايت',
                'short_ar' => 'آيفون 17 برو ماكس بشريحة eSIM وتخزين 1 تيرابايت. للمبدعين المحترفين الجادين.',
                'desc_ar'  => '<p>آبل آيفون 17 برو ماكس بتخزين 1 تيرابايت لمن يحتاج كل شيء وأكثر. مئات أفلام 4K، آلاف الصور RAW، ومكتبة موسيقى لا محدودة في جيبك.</p>',
            ],
            'Apple iPhone 17 Pro Max (eSIM) 2TB' => [
                'name'     => 'Apple iPhone 17 Pro Max (eSIM) 2TB',
                'name_ar'  => 'آبل آيفون 17 برو ماكس (eSIM) - 2 تيرابايت',
                'short_ar' => 'آيفون 17 برو ماكس بشريحة eSIM وتخزين 2 تيرابايت. الإصدار الأقوى على الإطلاق.',
                'desc_ar'  => '<p>آبل آيفون 17 برو ماكس بتخزين 2 تيرابايت هو الأقوى والأكثر تخزيناً في تاريخ آيفون. لمحترفي الإنتاج والإبداع الذين لا يقبلون أي حدود.</p>',
            ],

            // -----------------------------------------------------------------
            // Xiaomi Phones
            // -----------------------------------------------------------------
            'Xiaomi Redmi A3 3/64GB' => [
                'name'     => 'Xiaomi Redmi A3 3/64GB',
                'name_ar'  => 'شاومي ريدمي A3 - ذاكرة 3 جيجا / تخزين 64 جيجا',
                'short_ar' => 'هاتف شاومي ريدمي A3 بذاكرة 3 جيجا وتخزين 64 جيجابايت. البداية المثالية مع شاومي.',
                'desc_ar'  => '<p>شاومي ريدمي A3 هو الخيار المثالي للدخول إلى عالم الهواتف الذكية. شاشة واسعة وبطارية 5000 مللي أمبير وأداء موثوق بسعر لا يُصدق.</p>',
            ],
            'Xiaomi Redmi A5 3/64GB' => [
                'name'     => 'Xiaomi Redmi A5 3/64GB',
                'name_ar'  => 'شاومي ريدمي A5 - ذاكرة 3 جيجا / تخزين 64 جيجا',
                'short_ar' => 'هاتف شاومي ريدمي A5 بذاكرة 3 جيجا وتخزين 64 جيجابايت. ابتكار شاومي بسعر في متناول الجميع.',
                'desc_ar'  => '<p>شاومي ريدمي A5 يقدم تجربة هاتف ذكي حديثة بسعر اقتصادي. شاشة HD+ واسعة ومعالج قوي وبطارية تدوم طويلاً للاستخدام اليومي.</p>',
            ],
            'Xiaomi Redmi A5 4/128GB' => [
                'name'     => 'Xiaomi Redmi A5 4/128GB',
                'name_ar'  => 'شاومي ريدمي A5 - ذاكرة 4 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف شاومي ريدمي A5 المحسّن بذاكرة 4 جيجا وتخزين 128 جيجابايت. أداء أسرع ومساحة أوسع.',
                'desc_ar'  => '<p>شاومي ريدمي A5 بذاكرة 4 جيجا يتيح تعدد المهام بسلاسة أكبر. تخزين 128 جيجابايت كافٍ لكل تطبيقاتك وصورك ومقاطع الفيديو.</p>',
            ],
            'Xiaomi Redmi 15C 4/128GB' => [
                'name'     => 'Xiaomi Redmi 15C 4/128GB',
                'name_ar'  => 'شاومي ريدمي 15C - ذاكرة 4 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف شاومي ريدمي 15C بذاكرة 4 جيجا وتخزين 128 جيجابايت. ترقية ممتازة من الفئة الاقتصادية.',
                'desc_ar'  => '<p>شاومي ريدمي 15C يرفع مستوى الهواتف الاقتصادية. شاشة HD+ بحجم 6.88 بوصة وكاميرا 50 ميجابكسل وبطارية 5160 مللي أمبير.</p>',
            ],
            'Xiaomi Redmi 15C 6/128GB' => [
                'name'     => 'Xiaomi Redmi 15C 6/128GB',
                'name_ar'  => 'شاومي ريدمي 15C - ذاكرة 6 جيجا / تخزين 128 جيجا',
                'short_ar' => 'هاتف شاومي ريدمي 15C المحسّن بذاكرة 6 جيجا وتخزين 128 جيجابايت. أداء متوسطي بسعر اقتصادي.',
                'desc_ar'  => '<p>شاومي ريدمي 15C بذاكرة 6 جيجا يقدم أداءً أكثر سلاسة لتطبيقات التواصل الاجتماعي والألعاب الخفيفة. خيار ذكي لمن يريد الأفضل بأقل تكلفة.</p>',
            ],
            'Xiaomi Redmi Note 14 4G 8/256GB' => [
                'name'     => 'Xiaomi Redmi Note 14 4G 8/256GB',
                'name_ar'  => 'شاومي ريدمي نوت 14 4G - ذاكرة 8 جيجا / تخزين 256 جيجا',
                'short_ar' => 'هاتف شاومي ريدمي نوت 14 بذاكرة 8 جيجا وتخزين 256 جيجابايت. القوة والأداء في فئة نوت.',
                'desc_ar'  => '<p>شاومي ريدمي نوت 14 4G يأتي بمواصفات استثنائية. شاشة AMOLED FHD+ وكاميرا 108 ميجابكسل وذاكرة 8 جيجا للأداء الأمثل في المهام الثقيلة.</p>',
            ],
            'Xiaomi Redmi Note 13 Pro 5G 12/512GB' => [
                'name'     => 'Xiaomi Redmi Note 13 Pro 5G 12/512GB',
                'name_ar'  => 'شاومي ريدمي نوت 13 برو 5G - ذاكرة 12 جيجا / تخزين 512 جيجا',
                'short_ar' => 'هاتف شاومي ريدمي نوت 13 برو 5G بذاكرة 12 جيجا وتخزين 512 جيجابايت. الفلاجشيب الاقتصادي.',
                'desc_ar'  => '<p>شاومي ريدمي نوت 13 برو 5G يقدم مواصفات الفئة العليا. شريحة Snapdragon قوية وكاميرا 200 ميجابكسل بتقنية OIS وشاشة AMOLED 120Hz فائقة.</p>',
            ],

            // -----------------------------------------------------------------
            // Tablets
            // -----------------------------------------------------------------
            'Samsung Tab A11 8.7 WiFi 4/64GB' => [
                'name'     => 'Samsung Tab A11 8.7 WiFi 4/64GB',
                'name_ar'  => 'تابلت سامسونج جالاكسي تاب A11 بشاشة 8.7 بوصة WiFi - ذاكرة 4 جيجا / تخزين 64 جيجا',
                'short_ar' => 'تابلت سامسونج A11 بشاشة 8.7 بوصة وذاكرة 4 جيجا وتخزين 64 جيجابايت. مثالي للترفيه اليومي.',
                'desc_ar'  => '<p>تابلت سامسونج جالاكسي تاب A11 بشاشة 8.7 بوصة الواضحة مثالي للأطفال ومتابعة المحتوى والمهام اليومية. خفيف الوزن وسهل الحمل مع بطارية تدوم طويلاً.</p>',
            ],
            'Samsung Tab A11 10.9 WiFi 6/128GB' => [
                'name'     => 'Samsung Tab A11 10.9 WiFi 6/128GB',
                'name_ar'  => 'تابلت سامسونج جالاكسي تاب A11 بشاشة 10.9 بوصة WiFi - ذاكرة 6 جيجا / تخزين 128 جيجا',
                'short_ar' => 'تابلت سامسونج A11 بشاشة 10.9 بوصة وذاكرة 6 جيجا وتخزين 128 جيجابايت. مساحة عمل واسعة.',
                'desc_ar'  => '<p>تابلت سامسونج جالاكسي تاب A11 بشاشة 10.9 بوصة مثالي للعمل والترفيه. شاشة واسعة وواضحة مع صوت Dolby Atmos ممتاز لتجربة بث احترافية.</p>',
            ],
            'Samsung Tab S10 Plus WiFi 12/256GB' => [
                'name'     => 'Samsung Tab S10 Plus WiFi 12/256GB',
                'name_ar'  => 'تابلت سامسونج جالاكسي تاب S10 بلس WiFi - ذاكرة 12 جيجا / تخزين 256 جيجا',
                'short_ar' => 'تابلت سامسونج جالاكسي تاب S10 بلس بذاكرة 12 جيجا وتخزين 256 جيجابايت. الفلاجشيب المتميز.',
                'desc_ar'  => '<p>سامسونج جالاكسي تاب S10 بلس هو أفضل تابلت أندرويد على الإطلاق. شاشة Dynamic AMOLED 12.4 بوصة فائقة الجمال مع معالج Snapdragon Elite وقلم S Pen المتضمن.</p>',
            ],
            'Doogee U10 10" WiFi 4/128GB' => [
                'name'     => 'Doogee U10 10" WiFi 4/128GB',
                'name_ar'  => 'تابلت دوجي U10 بشاشة 10 بوصة WiFi - ذاكرة 4 جيجا / تخزين 128 جيجا',
                'short_ar' => 'تابلت دوجي U10 بشاشة 10 بوصة وذاكرة 4 جيجا وتخزين 128 جيجابايت. قيمة ممتازة.',
                'desc_ar'  => '<p>تابلت دوجي U10 يقدم شاشة 10 بوصة واسعة بسعر اقتصادي. مثالي للاستخدام المنزلي للأطفال والعائلة مع بطارية تدوم طويلاً وتصميم متين.</p>',
            ],
            'Teclast P30T WiFi 4/64GB' => [
                'name'     => 'Teclast P30T WiFi 4/64GB',
                'name_ar'  => 'تابلت تيكلاست P30T WiFi - ذاكرة 4 جيجا / تخزين 64 جيجا',
                'short_ar' => 'تابلت تيكلاست P30T بذاكرة 4 جيجا وتخزين 64 جيجابايت. تابلت اقتصادي بمواصفات جيدة.',
                'desc_ar'  => '<p>تابلت تيكلاست P30T خيار ممتاز للميزانية المحدودة. شاشة FHD+ واضحة ومعالج قوي وبطارية ضخمة للاستخدام الطويل. مثالي للقراءة والمشاهدة.</p>',
            ],

            // -----------------------------------------------------------------
            // Accessories
            // -----------------------------------------------------------------
            'Samsung Buds Core' => [
                'name'     => 'Samsung Buds Core',
                'name_ar'  => 'سماعات سامسونج جالاكسي بادز كور',
                'short_ar' => 'سماعات سامسونج جالاكسي بادز كور اللاسلكية. صوت نقي وراحة تدوم طويلاً بسعر مناسب.',
                'desc_ar'  => '<p>سامسونج جالاكسي بادز كور تقدم جودة صوت ممتازة من سامسونج بسعر في المتناول. تصميم مريح وبطارية تدوم 8 ساعات واتصال بلوتوث مستقر.</p>',
            ],
            'Samsung Buds 3 Pro' => [
                'name'     => 'Samsung Buds 3 Pro',
                'name_ar'  => 'سماعات سامسونج جالاكسي بادز 3 برو',
                'short_ar' => 'سماعات سامسونج جالاكسي بادز 3 برو مع إلغاء الضوضاء الذكي. صوت احترافي في أنحاء الأذن.',
                'desc_ar'  => '<p>سامسونج جالاكسي بادز 3 برو تقدم تجربة صوتية لا مثيل لها. تقنية إلغاء الضوضاء الذكي وصوت 360 درجة وبطارية 30 ساعة مع علبة الشحن للاستمتاع الكامل.</p>',
            ],
            'Samsung Galaxy Watch 7 Ultra 47mm' => [
                'name'     => 'Samsung Galaxy Watch 7 Ultra 47mm',
                'name_ar'  => 'ساعة سامسونج جالاكسي واتش 7 الترا 47 ملم',
                'short_ar' => 'ساعة سامسونج جالاكسي واتش 7 الترا بحجم 47 ملم. الأداء الأقوى في تاريخ ساعات سامسونج.',
                'desc_ar'  => '<p>سامسونج جالاكسي واتش 7 الترا مصممة للمغامرين والرياضيين. جهاز تتبع صحي متكامل بهيكل تيتانيوم متين وشاشة Sapphire وبطارية 60 ساعة.</p>',
            ],
            'Apple AirPods 3' => [
                'name'     => 'Apple AirPods 3',
                'name_ar'  => 'آبل إيربودز 3 (الجيل الثالث)',
                'short_ar' => 'آبل إيربودز الجيل الثالث. صوت مكاني ممتاز ومقاوم للماء وعمر بطارية 30 ساعة.',
                'desc_ar'  => '<p>آبل إيربودز الجيل الثالث يقدم تجربة صوتية استثنائية بتقنية الصوت المكاني Spatial Audio. مقاوم للماء والعرق مع بطارية 6 ساعات وعلبة شحن توفر 30 ساعة إضافية.</p>',
            ],
            'Apple AirPods Pro (ANC)' => [
                'name'     => 'Apple AirPods Pro (ANC)',
                'name_ar'  => 'آبل إيربودز برو (إلغاء الضوضاء)',
                'short_ar' => 'آبل إيربودز برو مع تقنية إلغاء الضوضاء الذكي. أفضل سماعات آبل بصوت احترافي.',
                'desc_ar'  => '<p>آبل إيربودز برو يجمع إلغاء الضوضاء النشط الاستثنائي مع الصوت المكاني الغامر. شريحة H2 تضمن أداءً صوتياً لا مثيل له مع عمر بطارية 30 ساعة.</p>',
            ],
            'Apple AirTag 4 Pack' => [
                'name'     => 'Apple AirTag 4 Pack',
                'name_ar'  => 'آبل إيرتاج - حزمة 4 قطع',
                'short_ar' => 'آبل إيرتاج حزمة 4 قطع. تتبع مفاتيحك وحقيبتك وأي شيء ثمين بسهولة.',
                'desc_ar'  => '<p>آبل إيرتاج حزمة 4 قطع تساعدك على تتبع أغراضك الثمينة بدقة عالية. يعمل مع شبكة Find My المكونة من مليار جهاز آبل حول العالم للعثور على أغراضك أينما كانت.</p>',
            ],
            'Apple Pencil 2' => [
                'name'     => 'Apple Pencil 2',
                'name_ar'  => 'قلم آبل 2 (الجيل الثاني)',
                'short_ar' => 'قلم آبل الجيل الثاني. مثالي للرسم والكتابة على iPad Pro وiPad Air.',
                'desc_ar'  => '<p>قلم آبل الجيل الثاني يرفع تجربة الإبداع على iPad إلى مستوى جديد. يدعم الحساسية للضغط والإمالة بالكامل مع شحن مغناطيسي لاسلكي وتأخير منخفض للغاية.</p>',
            ],

            // -----------------------------------------------------------------
            // Gaming
            // -----------------------------------------------------------------
            'Sony PS5 1TB' => [
                'name'     => 'Sony PS5 1TB',
                'name_ar'  => 'سوني بلايستيشن 5 - 1 تيرابايت',
                'short_ar' => 'سوني بلايستيشن 5 بتخزين 1 تيرابايت. جهاز الألعاب الأقوى من سوني بألعاب استثنائية.',
                'desc_ar'  => '<p>سوني بلايستيشن 5 يعيد تعريف تجربة الألعاب بمعالج رسوميات مخصص وذاكرة SSD فائقة السرعة وتقنية Ray Tracing. يدعم ألعاب 4K بمعدل 120 إطار ووحدة تحكم DualSense المميزة.</p>',
            ],
        ];
    }

    // =========================================================================
    // Product Image URL Map
    // =========================================================================

    private function getImageUrls(): array
    {
        return [
            // Samsung phones – GSMArena CDN (official manufacturer press images)
            'Galaxy A06 5G'         => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a06-5g.jpg',
            'Galaxy A07'            => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a07.jpg',
            'Galaxy A16'            => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a16-5g.jpg',
            'Galaxy A17'            => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a17-5g.jpg',
            'Galaxy A26'            => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a26.jpg',
            'Galaxy A36'            => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a36.jpg',
            'Galaxy A56'            => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a56-.jpg',

            // Tecno phones
            'Spark Go One'          => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-go1.jpg',
            'Spark Go 2'            => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-go2.jpg',
            'Spark Go 3'            => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-go3.jpg',
            'Spark 4C'              => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-40c.jpg',
            'Spark 20 Pro'          => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-20-pro.jpg',
            'Spark Slim'            => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-slim.jpg',
            'Pova 7 5G'             => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-pova7-5g.jpg',
            'Pova 7'                => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-pova7.jpg',
            'Camon 40 Pro 5G'       => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-camon-40-pro-5g.jpg',
            'Camon 20 Premier'      => 'https://fdn2.gsmarena.com/vv/bigpic/tecno-camon20-premier-5g.jpg',

            // Apple iPhones
            'iPhone 15 Pro'         => 'https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-15-pro.jpg',
            'iPhone 16'             => 'https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-16.jpg',
            'iPhone 17 Air'         => 'https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-air.jpg',
            'iPhone 17 Pro Max'     => 'https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-17-pro-max.jpg',
            'iPhone 17 Pro'         => 'https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-17-pro.jpg',
            'iPhone 17'             => 'https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-17.jpg',

            // Xiaomi
            'Redmi A3'              => 'https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-a3.jpg',
            'Redmi A5'              => 'https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-a5-4g.jpg',
            'Redmi 15C'             => 'https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-15c-4g.jpg',
            'Redmi Note 14 4G'      => 'https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-note-14-4g-gl.jpg',
            'Redmi Note 13 Pro 5G'  => 'https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-note-13-pro-plus-int.jpg',

            // Tablets
            'Tab S10 Plus'          => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-tab-s10-plus.jpg',
            'Tab A11'               => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-tab-a11.jpg',
            'Doogee U10'            => 'https://fdn2.gsmarena.com/vv/bigpic/doogee-u10.jpg',
            'Teclast P30T'          => 'https://fdn2.gsmarena.com/vv/bigpic/teclast-p30t.jpg',

            // Accessories
            'Buds Core'             => 'https://images.samsung.com/is/image/samsung/p6pim/levant/sm-r410nzkamea/gallery/levant-galaxy-buds-core-r410-sm-r410nzkamea-thumb-547814709',
            'Buds 3 Pro'            => 'https://images.samsung.com/is/image/samsung/p6pim/levant/2407/gallery/levant-galaxy-buds3-pro-r630-sm-r630nzwamea-thumb-542135718',
            'Galaxy Watch 7 Ultra'  => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-watch-ultra.jpg',
            'AirPods 3'             => 'https://fdn2.gsmarena.com/vv/bigpic/apple-airpods-3rd-gen-.jpg',
            'AirPods Pro'           => 'https://fdn2.gsmarena.com/vv/bigpic/apple-airpods-pro-2nd-gen.jpg',
            'AirTag'                => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/airtag-1pack-select-202601?wid=600&hei=600&fmt=jpeg&qlt=90',
            'Apple Pencil 2'        => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/MU8F2?wid=2000&hei=2000&fmt=jpeg&qlt=90',

            // Gaming
            'PS5'                   => 'https://gmedia.playstation.com/is/image/SIEPDC/ps5-slim-disc-console-featured-hardware-image-block-02-en-15nov23?fmt=jpeg&w=600',
        ];
    }
}
