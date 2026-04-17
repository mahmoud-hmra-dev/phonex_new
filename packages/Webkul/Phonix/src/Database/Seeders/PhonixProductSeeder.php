<?php

namespace Webkul\Phonix\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhonixProductSeeder extends Seeder
{
    /**
     * Attribute type to value column mapping.
     */
    protected array $typeColumnMap = [
        'text'     => 'text_value',
        'textarea' => 'text_value',
        'price'    => 'float_value',
        'boolean'  => 'boolean_value',
        'select'   => 'integer_value',
        'date'     => 'date_value',
    ];

    /**
     * Null columns template for product_attribute_values.
     */
    protected array $nullColumns = [
        'text_value'     => null,
        'boolean_value'  => null,
        'integer_value'  => null,
        'float_value'    => null,
        'datetime_value' => null,
        'date_value'     => null,
        'json_value'     => null,
    ];

    protected string $timestamp;

    /**
     * Cached brand option IDs keyed by brand name.
     */
    protected array $brandOptions = [];

    /**
     * Cached category IDs keyed by category name.
     */
    protected array $categoryIds = [];

    public function run(): void
    {
        $this->timestamp = Carbon::now()->format('Y-m-d H:i:s');

        $this->command?->info('Creating brand options...');
        $this->createBrandOptions();

        $this->command?->info('Creating categories...');
        $this->createCategories();

        $this->command?->info('Creating products...');
        $products = $this->getProductDefinitions();

        $createdCount = 0;

        foreach ($products as $productDef) {
            $this->createProduct($productDef);
            $createdCount++;
        }

        $this->command?->info("Created {$createdCount} products across " . count($this->categoryIds) . ' categories with ' . count($this->brandOptions) . ' brands.');
        $this->command?->info('Running product flat indexer...');

        try {
            $this->reindexFlat();
            $this->command?->info('Product flat index rebuilt.');
        } catch (\Throwable $e) {
            $this->command?->warn('Flat indexing failed: ' . $e->getMessage());
            $this->command?->warn('Run "php artisan bagisto:product:reindex" manually.');
        }
    }

    // =========================================================================
    // Brand Options
    // =========================================================================

    protected function createBrandOptions(): void
    {
        $brands = ['Samsung', 'Tecno', 'Apple', 'Xiaomi', 'Doogee', 'Teclast', 'Sony', 'JBL'];

        foreach ($brands as $i => $brand) {
            $existing = DB::table('attribute_options')
                ->where('attribute_id', 25)
                ->where('admin_name', $brand)
                ->first();

            if ($existing) {
                $this->brandOptions[$brand] = $existing->id;
                continue;
            }

            $optionId = DB::table('attribute_options')->insertGetId([
                'attribute_id' => 25,
                'admin_name'   => $brand,
                'sort_order'   => $i + 1,
            ]);

            DB::table('attribute_option_translations')->insert([
                'attribute_option_id' => $optionId,
                'locale'              => 'en',
                'label'               => $brand,
            ]);

            $this->brandOptions[$brand] = $optionId;
        }
    }

    // =========================================================================
    // Categories
    // =========================================================================

    protected function createCategories(): void
    {
        $categories = [
            'Samsung Phones',
            'Tecno Phones',
            'Apple iPhones',
            'Xiaomi Phones',
            'Tablets',
            'Accessories',
            'Gaming',
        ];

        foreach ($categories as $i => $name) {
            $slug = Str::slug($name);

            // Check if category already exists by slug in translations
            $existing = DB::table('category_translations')
                ->where('slug', $slug)
                ->where('locale', 'en')
                ->first();

            if ($existing) {
                $this->categoryIds[$name] = $existing->category_id;
                continue;
            }

            $categoryId = DB::table('categories')->insertGetId([
                'position'     => $i + 1,
                'status'       => 1,
                'parent_id'    => 1,
                'display_mode' => 'products_and_description',
                '_lft'         => 0,
                '_rgt'         => 0,
                'created_at'   => $this->timestamp,
                'updated_at'   => $this->timestamp,
            ]);

            DB::table('category_translations')->insert([
                'category_id' => $categoryId,
                'locale'      => 'en',
                'name'        => $name,
                'slug'        => $slug,
                'description' => $name . ' available at Phonix Store',
            ]);

            $this->categoryIds[$name] = $categoryId;
        }

        // Fix nested set tree
        try {
            \Webkul\Category\Models\Category::fixTree();
        } catch (\Throwable $e) {
            // Silently continue if model not available
        }
    }

    // =========================================================================
    // Product Creation
    // =========================================================================

    protected function createProduct(array $def): void
    {
        $sku = $def['sku'];

        // Skip if product with this SKU already exists
        if (DB::table('products')->where('sku', $sku)->exists()) {
            return;
        }

        $productId = DB::table('products')->insertGetId([
            'type'                => 'simple',
            'sku'                 => $sku,
            'attribute_family_id' => 1,
            'parent_id'           => null,
            'created_at'          => $this->timestamp,
            'updated_at'          => $this->timestamp,
        ]);

        // Set attribute values
        $this->setAttributeValue($productId, 1, 'text', $sku);                                     // sku
        $this->setAttributeValue($productId, 2, 'text', $def['name'], locale: 'en');                // name
        $this->setAttributeValue($productId, 3, 'text', $def['url_key'], locale: 'en');             // url_key
        $this->setAttributeValue($productId, 5, 'boolean', $def['new'] ?? 0);                       // new
        $this->setAttributeValue($productId, 6, 'boolean', $def['featured'] ?? 0);                  // featured
        $this->setAttributeValue($productId, 7, 'boolean', 1);                                      // visible_individually
        $this->setAttributeValue($productId, 8, 'boolean', 1, channel: 'default');                   // status
        $this->setAttributeValue($productId, 9, 'textarea', $def['short_description'], locale: 'en'); // short_description
        $this->setAttributeValue($productId, 10, 'textarea', $def['description'], locale: 'en');      // description
        $this->setAttributeValue($productId, 11, 'price', $def['price']);                             // price
        $this->setAttributeValue($productId, 22, 'text', $def['weight'] ?? '0.5');                    // weight
        $this->setAttributeValue($productId, 27, 'text', $def['product_number'] ?? $sku);             // product_number

        if (isset($def['special_price']) && $def['special_price'] > 0) {
            $this->setAttributeValue($productId, 13, 'price', $def['special_price']);                 // special_price
        }

        if (isset($def['brand']) && isset($this->brandOptions[$def['brand']])) {
            $this->setAttributeValue($productId, 25, 'select', $this->brandOptions[$def['brand']]);  // brand
        }

        // Assign to channel
        DB::table('product_channels')->insert([
            'product_id' => $productId,
            'channel_id' => 1,
        ]);

        // Assign to categories (root + specific)
        $categoryAssignments = [
            ['product_id' => $productId, 'category_id' => 1],
        ];

        if (isset($def['category']) && isset($this->categoryIds[$def['category']])) {
            $categoryAssignments[] = [
                'product_id'  => $productId,
                'category_id' => $this->categoryIds[$def['category']],
            ];
        }

        DB::table('product_categories')->insert($categoryAssignments);

        // Add inventory
        DB::table('product_inventories')->insert([
            'qty'                 => $def['qty'] ?? 100,
            'product_id'          => $productId,
            'inventory_source_id' => 1,
            'vendor_id'           => 0,
        ]);

        // Download and save product image
        $this->downloadProductImage($productId, $def['name']);
    }

    /**
     * Insert a single attribute value row.
     */
    protected function setAttributeValue(
        int $productId,
        int $attributeId,
        string $type,
        mixed $value,
        ?string $channel = null,
        ?string $locale = null,
    ): void {
        $uniqueId = implode('|', [
            $channel ?? '',
            $locale ?? '',
            $productId,
            $attributeId,
        ]);

        $valueColumn = $this->typeColumnMap[$type] ?? 'text_value';

        $row = array_merge($this->nullColumns, [
            'attribute_id' => $attributeId,
            'product_id'   => $productId,
            'channel'      => $channel,
            'locale'       => $locale,
            'unique_id'    => $uniqueId,
            $valueColumn   => $value,
        ]);

        DB::table('product_attribute_values')->insert($row);
    }

    // =========================================================================
    // Product Images
    // =========================================================================

    protected function downloadProductImage(int $productId, string $productName): void
    {
        try {
            $text = urlencode($productName);
            $imageUrl = "https://fakeimg.pl/500x500/1a8a96/ffffff/?text={$text}&font=noto";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $imageContent = @file_get_contents($imageUrl, false, $context);

            if ($imageContent === false) {
                // Fallback: create a simple placeholder
                $imageContent = $this->generatePlaceholderImage($productName);

                if ($imageContent === null) {
                    return;
                }
            }

            $imagePath = 'product/' . $productId . '/' . Str::random(20) . '.webp';
            Storage::disk('public')->put($imagePath, $imageContent);

            DB::table('product_images')->insert([
                'type'       => 'images',
                'path'       => 'product/' . $productId . '/' . basename($imagePath),
                'product_id' => $productId,
                'position'   => 1,
            ]);
        } catch (\Throwable $e) {
            // Skip image on failure, product is still created
        }
    }

    /**
     * Generate a simple placeholder image using GD if available.
     */
    protected function generatePlaceholderImage(string $text): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $img = imagecreatetruecolor(500, 500);
        $bg = imagecolorallocate($img, 26, 138, 150);     // #1a8a96
        $white = imagecolorallocate($img, 255, 255, 255);

        imagefill($img, 0, 0, $bg);

        // Wrap text if too long
        $fontSize = 4; // Built-in font size (1-5)
        $charWidth = imagefontwidth($fontSize);
        $maxChars = (int) (480 / $charWidth);
        $wrapped = wordwrap($text, $maxChars, "\n", true);
        $lines = explode("\n", $wrapped);

        $lineHeight = imagefontheight($fontSize) + 4;
        $totalHeight = count($lines) * $lineHeight;
        $startY = (int) ((500 - $totalHeight) / 2);

        foreach ($lines as $i => $line) {
            $lineWidth = strlen($line) * $charWidth;
            $x = (int) ((500 - $lineWidth) / 2);
            $y = $startY + ($i * $lineHeight);
            imagestring($img, $fontSize, $x, $y, $line, $white);
        }

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return $data ?: null;
    }

    // =========================================================================
    // Flat Indexer
    // =========================================================================

    protected function reindexFlat(): void
    {
        if (class_exists(\Webkul\Product\Helpers\Indexers\Flat::class)) {
            $indexer = app(\Webkul\Product\Helpers\Indexers\Flat::class);

            $products = DB::table('products')->get();

            foreach ($products->chunk(50) as $chunk) {
                $productModels = \Webkul\Product\Models\Product::whereIn('id', $chunk->pluck('id'))->get();
                $indexer->reindexBatch($productModels);
            }
        }
    }

    // =========================================================================
    // Product Definitions
    // =========================================================================

    protected function getProductDefinitions(): array
    {
        $products = [];

        // =====================================================================
        // Samsung Phones
        // =====================================================================
        $samsungPhones = [
            ['name' => 'Galaxy A06 5G', 'spec' => '4/128GB', 'price' => 94],
            ['name' => 'Galaxy A07 4G', 'spec' => '4/64GB', 'price' => 95],
            ['name' => 'Galaxy A07 4G', 'spec' => '4/128GB', 'price' => 105],
            ['name' => 'Galaxy A16', 'spec' => '8/256GB', 'price' => 180],
            ['name' => 'Galaxy A17', 'spec' => '4/128GB', 'price' => 152],
            ['name' => 'Galaxy A17', 'spec' => '6/128GB', 'price' => 168],
            ['name' => 'Galaxy A17', 'spec' => '8/256GB', 'price' => 200],
            ['name' => 'Galaxy A26', 'spec' => '6/128GB', 'price' => 182],
            ['name' => 'Galaxy A26', 'spec' => '8/256GB', 'price' => 238],
            ['name' => 'Galaxy A36', 'spec' => '8/256GB', 'price' => 288],
            ['name' => 'Galaxy A56', 'spec' => '8/128GB', 'price' => 320],
            ['name' => 'Galaxy A56', 'spec' => '8/256GB', 'price' => 377],
        ];

        foreach ($samsungPhones as $phone) {
            $fullName = 'Samsung ' . $phone['name'] . ' ' . $phone['spec'];
            $products[] = $this->buildProduct($fullName, $phone['price'], 'Samsung', 'Samsung Phones', [
                'new' => $phone['price'] >= 288 ? 1 : 0,
                'featured' => $phone['price'] >= 200 ? 1 : 0,
                'weight' => '0.2',
                'short_description' => "Samsung {$phone['name']} with {$phone['spec']} RAM/Storage. Reliable Samsung quality.",
                'description' => "<p>The Samsung {$phone['name']} ({$phone['spec']}) delivers outstanding performance with Samsung's trusted build quality. Features a stunning display, powerful processor, and long-lasting battery life. Perfect for everyday use and beyond.</p>",
            ]);
        }

        // =====================================================================
        // Tecno Phones
        // =====================================================================
        $tecnoPhones = [
            ['name' => 'Spark Go One', 'spec' => '3/64GB', 'price' => 71],
            ['name' => 'Spark Go One', 'spec' => '4/128GB', 'price' => 81],
            ['name' => 'Spark Go 2', 'spec' => '3/64GB', 'price' => 71],
            ['name' => 'Spark Go 3', 'spec' => '4/64GB', 'price' => 86],
            ['name' => 'Spark 4C', 'spec' => '8/256GB', 'price' => 111],
            ['name' => 'Spark 20 Pro', 'spec' => '12/256GB', 'price' => 149],
            ['name' => 'Spark Slim', 'spec' => '8/256GB', 'price' => 190],
            ['name' => 'Pova 7', 'spec' => '8/256GB', 'price' => 160],
            ['name' => 'Pova 7 5G', 'spec' => '8/256GB', 'price' => 217],
            ['name' => 'Camon 20 Premier', 'spec' => '8/512GB', 'price' => 325],
            ['name' => 'Camon 40 Pro 5G', 'spec' => '12/256GB', 'price' => 291],
        ];

        foreach ($tecnoPhones as $phone) {
            $fullName = 'Tecno ' . $phone['name'] . ' ' . $phone['spec'];
            $products[] = $this->buildProduct($fullName, $phone['price'], 'Tecno', 'Tecno Phones', [
                'new' => $phone['price'] >= 200 ? 1 : 0,
                'featured' => $phone['price'] >= 149 ? 1 : 0,
                'weight' => '0.2',
                'short_description' => "Tecno {$phone['name']} with {$phone['spec']} RAM/Storage. Great value smartphone.",
                'description' => "<p>The Tecno {$phone['name']} ({$phone['spec']}) offers incredible value with impressive specs. Featuring a vibrant display, capable cameras, and all-day battery life. An excellent choice for budget-conscious buyers.</p>",
            ]);
        }

        // =====================================================================
        // Apple iPhones
        // =====================================================================
        $iphones = [
            ['name' => 'iPhone 15 Pro (SIM)', 'spec' => '256GB', 'price' => 1100],
            ['name' => 'iPhone 16 (SIM)', 'spec' => '128GB', 'price' => 740],
            ['name' => 'iPhone 17 (eSIM)', 'spec' => '256GB', 'price' => 930],
            ['name' => 'iPhone 17 (SIM)', 'spec' => '256GB', 'price' => 965],
            ['name' => 'iPhone 17 Air (eSIM)', 'spec' => '256GB', 'price' => 1030],
            ['name' => 'iPhone 17 Air (eSIM)', 'spec' => '512GB', 'price' => 1240],
            ['name' => 'iPhone 17 Pro (eSIM)', 'spec' => '256GB', 'price' => 1400],
            ['name' => 'iPhone 17 Pro (eSIM)', 'spec' => '512GB', 'price' => 1580],
            ['name' => 'iPhone 17 Pro Max (eSIM)', 'spec' => '256GB', 'price' => 1520],
            ['name' => 'iPhone 17 Pro Max (eSIM)', 'spec' => '512GB', 'price' => 1750],
            ['name' => 'iPhone 17 Pro Max (eSIM)', 'spec' => '1TB', 'price' => 2000],
            ['name' => 'iPhone 17 Pro Max (eSIM)', 'spec' => '2TB', 'price' => 2200],
        ];

        foreach ($iphones as $phone) {
            $fullName = 'Apple ' . $phone['name'] . ' ' . $phone['spec'];
            $products[] = $this->buildProduct($fullName, $phone['price'], 'Apple', 'Apple iPhones', [
                'new' => str_contains($phone['name'], '17') ? 1 : 0,
                'featured' => 1,
                'weight' => '0.2',
                'short_description' => "Apple {$phone['name']} with {$phone['spec']} storage. Premium Apple experience.",
                'description' => "<p>The Apple {$phone['name']} ({$phone['spec']}) delivers the ultimate iPhone experience. Featuring Apple's most advanced chip, pro-level camera system, and stunning Super Retina display. Built for those who demand the very best.</p>",
            ]);
        }

        // =====================================================================
        // Xiaomi Phones
        // =====================================================================
        $xiaomiPhones = [
            ['name' => 'Redmi A3', 'spec' => '3/64GB', 'price' => 63],
            ['name' => 'Redmi A5', 'spec' => '3/64GB', 'price' => 75],
            ['name' => 'Redmi A5', 'spec' => '4/128GB', 'price' => 89],
            ['name' => 'Redmi 15C', 'spec' => '4/128GB', 'price' => 112],
            ['name' => 'Redmi 15C', 'spec' => '6/128GB', 'price' => 135],
            ['name' => 'Redmi Note 14 4G', 'spec' => '8/256GB', 'price' => 178],
            ['name' => 'Redmi Note 13 Pro 5G', 'spec' => '12/512GB', 'price' => 280],
        ];

        foreach ($xiaomiPhones as $phone) {
            $fullName = 'Xiaomi ' . $phone['name'] . ' ' . $phone['spec'];
            $products[] = $this->buildProduct($fullName, $phone['price'], 'Xiaomi', 'Xiaomi Phones', [
                'new' => $phone['price'] >= 178 ? 1 : 0,
                'featured' => $phone['price'] >= 135 ? 1 : 0,
                'weight' => '0.2',
                'short_description' => "Xiaomi {$phone['name']} with {$phone['spec']} RAM/Storage. Innovation for everyone.",
                'description' => "<p>The Xiaomi {$phone['name']} ({$phone['spec']}) combines cutting-edge technology with unbeatable value. Features a high-refresh display, capable processor, and Xiaomi's MIUI experience. Smart choice for tech enthusiasts.</p>",
            ]);
        }

        // =====================================================================
        // Tablets
        // =====================================================================
        $tablets = [
            ['name' => 'Samsung Tab A11 8.7 WiFi', 'spec' => '4/64GB', 'price' => 113, 'brand' => 'Samsung'],
            ['name' => 'Samsung Tab A11 10.9 WiFi', 'spec' => '6/128GB', 'price' => 211, 'brand' => 'Samsung'],
            ['name' => 'Samsung Tab S10 Plus WiFi', 'spec' => '12/256GB', 'price' => 800, 'brand' => 'Samsung'],
            ['name' => 'Doogee U10 10" WiFi', 'spec' => '4/128GB', 'price' => 70, 'brand' => 'Doogee'],
            ['name' => 'Teclast P30T WiFi', 'spec' => '4/64GB', 'price' => 89, 'brand' => 'Teclast'],
        ];

        foreach ($tablets as $tablet) {
            $fullName = $tablet['name'] . ' ' . $tablet['spec'];
            $products[] = $this->buildProduct($fullName, $tablet['price'], $tablet['brand'], 'Tablets', [
                'new' => $tablet['price'] >= 211 ? 1 : 0,
                'featured' => $tablet['price'] >= 113 ? 1 : 0,
                'weight' => '0.5',
                'short_description' => "{$tablet['name']} with {$tablet['spec']}. Perfect for work and entertainment.",
                'description' => "<p>The {$tablet['name']} ({$tablet['spec']}) is your perfect companion for productivity and entertainment. Features a spacious display, reliable performance, and long battery life. Ideal for browsing, streaming, and getting things done.</p>",
            ]);
        }

        // =====================================================================
        // Accessories
        // =====================================================================
        $accessories = [
            ['name' => 'Samsung Buds Core', 'price' => 35, 'brand' => 'Samsung', 'weight' => '0.05'],
            ['name' => 'Samsung Buds 3 Pro', 'price' => 169, 'brand' => 'Samsung', 'weight' => '0.05'],
            ['name' => 'Samsung Galaxy Watch 7 Ultra 47mm', 'price' => 305, 'brand' => 'Samsung', 'weight' => '0.1'],
            ['name' => 'Apple AirPods 3', 'price' => 102, 'brand' => 'Apple', 'weight' => '0.05'],
            ['name' => 'Apple AirPods Pro (ANC)', 'price' => 148, 'brand' => 'Apple', 'weight' => '0.05'],
            ['name' => 'Apple AirTag 4 Pack', 'price' => 78, 'brand' => 'Apple', 'weight' => '0.1'],
            ['name' => 'Apple Pencil 2', 'price' => 79, 'brand' => 'Apple', 'weight' => '0.05'],
        ];

        foreach ($accessories as $acc) {
            $products[] = $this->buildProduct($acc['name'], $acc['price'], $acc['brand'], 'Accessories', [
                'new' => $acc['price'] >= 148 ? 1 : 0,
                'featured' => $acc['price'] >= 102 ? 1 : 0,
                'weight' => $acc['weight'],
                'short_description' => "{$acc['name']}. Premium accessory from {$acc['brand']}.",
                'description' => "<p>The {$acc['name']} is a premium accessory that enhances your digital experience. Crafted with quality materials and cutting-edge technology by {$acc['brand']}. A must-have addition to your tech collection.</p>",
            ]);
        }

        // =====================================================================
        // Gaming
        // =====================================================================
        $products[] = $this->buildProduct('Sony PS5 1TB', 600, 'Sony', 'Gaming', [
            'new' => 1,
            'featured' => 1,
            'weight' => '4.5',
            'short_description' => 'Sony PlayStation 5 with 1TB storage. Next-gen gaming console.',
            'description' => '<p>The Sony PlayStation 5 (1TB) delivers breathtaking gaming experiences with lightning-fast loading, stunning graphics powered by ray tracing, and an immersive DualSense controller. Features 1TB SSD storage for your growing game library. The ultimate gaming console.</p>',
        ]);

        return $products;
    }

    /**
     * Build a standardized product definition array.
     */
    protected function buildProduct(string $name, float $price, string $brand, string $category, array $extra = []): array
    {
        $urlKey = Str::slug($name);

        // Ensure unique URL key by appending price if slug is duplicate
        static $usedUrlKeys = [];

        if (in_array($urlKey, $usedUrlKeys)) {
            $urlKey .= '-' . (int) $price;
        }

        $usedUrlKeys[] = $urlKey;

        return array_merge([
            'name'              => $name,
            'sku'               => strtoupper(Str::slug($name, '-')),
            'url_key'           => $urlKey,
            'price'             => $price,
            'brand'             => $brand,
            'category'          => $category,
            'product_number'    => 'PHX-' . strtoupper(Str::random(6)),
            'new'               => 0,
            'featured'          => 0,
            'weight'            => '0.2',
            'qty'               => 100,
            'short_description' => $name . '. Available at Phonix Store.',
            'description'       => '<p>' . $name . ' - Available now at Phonix electronics store. Quality guaranteed.</p>',
        ], $extra);
    }
}
