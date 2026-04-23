<?php

namespace Webkul\Phonix\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhonixProductSeeder extends Seeder
{
    protected array $typeColumnMap = [
        'text'     => 'text_value',
        'textarea' => 'text_value',
        'price'    => 'float_value',
        'boolean'  => 'boolean_value',
        'select'   => 'integer_value',
        'date'     => 'date_value',
    ];

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
    protected array $brandOptions  = [];
    protected array $categoryIds   = [];
    protected int   $ramAttrId     = 0;
    protected int   $storageAttrId = 0;
    protected array $ramOptions    = [];
    protected array $storageOptions = [];

    public function run(): void
    {
        $this->timestamp = Carbon::now()->format('Y-m-d H:i:s');

        $this->command?->info('Creating brand options...');
        $this->createBrandOptions();

        $this->command?->info('Creating categories...');
        $this->createCategories();

        $this->command?->info('Creating variant attributes (RAM, Storage)...');
        $this->createPhoneAttributes();

        $this->command?->info('Seeding products...');
        $count = $this->seedProducts();
        $this->command?->info("Done — processed {$count} product groups.");

        $this->command?->info('Running flat indexer...');
        try {
            $this->reindexFlat();
            $this->command?->info('Flat index rebuilt.');
        } catch (\Throwable $e) {
            $this->command?->warn('Flat indexing skipped: ' . $e->getMessage());
            $this->command?->warn('Run "php artisan bagisto:product:reindex" manually if needed.');
        }
    }

    // =========================================================================
    // Brand Options
    // =========================================================================

    protected function createBrandOptions(): void
    {
        $brands = ['Samsung', 'Tecno', 'Apple', 'Xiaomi', 'Doogee', 'Teclast', 'Sony'];

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

        try {
            \Webkul\Category\Models\Category::fixTree();
        } catch (\Throwable $e) {
        }
    }

    // =========================================================================
    // Phone Variant Attributes (RAM + Storage)
    // =========================================================================

    protected function createPhoneAttributes(): void
    {
        $this->ramAttrId     = $this->findOrCreateAttribute('ram', 'RAM', ['3GB', '4GB', '6GB', '8GB', '12GB', '16GB']);
        $this->storageAttrId = $this->findOrCreateAttribute('storage', 'Storage', ['64GB', '128GB', '256GB', '512GB', '1TB', '2TB']);
    }

    protected function findOrCreateAttribute(string $code, string $label, array $optionLabels): int
    {
        $existing = DB::table('attributes')->where('code', $code)->first();

        if ($existing) {
            $attrId = $existing->id;
        } else {
            $attrId = DB::table('attributes')->insertGetId([
                'code'                => $code,
                'admin_name'          => $label,
                'type'                => 'select',
                'position'            => 0,
                'is_required'         => 0,
                'is_unique'           => 0,
                'validation'          => null,
                'is_configurable'     => 1,
                'is_filterable'       => 1,
                'is_comparable'       => 0,
                'is_visible_on_front' => 1,
                'is_user_defined'     => 1,
                'swatch_type'         => null,
                'created_at'          => $this->timestamp,
                'updated_at'          => $this->timestamp,
            ]);

            DB::table('attribute_translations')->insert([
                'attribute_id' => $attrId,
                'locale'       => 'en',
                'name'         => $label,
            ]);

            $group = DB::table('attribute_groups')
                ->where('attribute_family_id', 1)
                ->orderBy('id')
                ->first();

            if ($group) {
                $maxPos = DB::table('attribute_group_mappings')
                    ->where('attribute_group_id', $group->id)
                    ->max('position') ?? 0;

                DB::table('attribute_group_mappings')->insert([
                    'attribute_group_id' => $group->id,
                    'attribute_id'       => $attrId,
                    'position'           => $maxPos + 1,
                ]);
            }
        }

        $options = [];
        foreach ($optionLabels as $i => $optLabel) {
            $existingOpt = DB::table('attribute_options')
                ->where('attribute_id', $attrId)
                ->where('admin_name', $optLabel)
                ->first();

            if ($existingOpt) {
                $options[$optLabel] = $existingOpt->id;
                continue;
            }

            $optId = DB::table('attribute_options')->insertGetId([
                'attribute_id' => $attrId,
                'admin_name'   => $optLabel,
                'sort_order'   => $i + 1,
            ]);

            DB::table('attribute_option_translations')->insert([
                'attribute_option_id' => $optId,
                'locale'              => 'en',
                'label'               => $optLabel,
            ]);

            $options[$optLabel] = $optId;
        }

        if ($code === 'ram') {
            $this->ramOptions = $options;
        } else {
            $this->storageOptions = $options;
        }

        return $attrId;
    }

    // =========================================================================
    // Seed Products
    // =========================================================================

    protected function seedProducts(): int
    {
        $count = 0;

        foreach ($this->getSamsungGroups() as $group) {
            $this->seedGroup($group);
            $count++;
        }

        foreach ($this->getTecnoGroups() as $group) {
            $this->seedGroup($group);
            $count++;
        }

        foreach ($this->getAppleGroups() as $group) {
            $this->seedGroup($group);
            $count++;
        }

        foreach ($this->getXiaomiGroups() as $group) {
            $this->seedGroup($group);
            $count++;
        }

        foreach ($this->getTabletGroups() as $group) {
            $this->seedGroup($group);
            $count++;
        }

        foreach ($this->getAccessories() as $def) {
            $this->createSimpleProduct($def);
            $count++;
        }

        foreach ($this->getGamingProducts() as $def) {
            $this->createSimpleProduct($def);
            $count++;
        }

        return $count;
    }

    protected function seedGroup(array $group): void
    {
        $variants = $group['variants'] ?? [];

        if (count($variants) > 1) {
            $this->createConfigurableProduct($group);
        } else {
            $v       = $variants[0] ?? [];
            $ram     = $v['ram'] ?? '';
            $storage = $v['storage'] ?? '';
            $parts   = array_filter([$ram, $storage]);
            $spec    = implode('/', $parts);
            $name    = $spec ? $group['name'] . ' ' . $spec : $group['name'];

            $this->createSimpleProduct(array_merge($group, [
                'name'  => $name,
                'price' => $v['price'] ?? ($group['price'] ?? 0),
            ]));
        }
    }

    // =========================================================================
    // Configurable Product
    // =========================================================================

    protected function createConfigurableProduct(array $group): void
    {
        $sku = strtoupper(Str::slug($group['name'], '-'));

        if (DB::table('products')->where('sku', $sku)->exists()) {
            return;
        }

        $minPrice = min(array_column($group['variants'], 'price'));

        $parentId = DB::table('products')->insertGetId([
            'type'                => 'configurable',
            'sku'                 => $sku,
            'attribute_family_id' => 1,
            'parent_id'           => null,
            'created_at'          => $this->timestamp,
            'updated_at'          => $this->timestamp,
        ]);

        $urlKey = Str::slug($group['name']);

        $this->setAttr($parentId, 1, 'text', $sku);
        $this->setAttr($parentId, 2, 'text', $group['name'], locale: 'en');
        $this->setAttr($parentId, 3, 'text', $urlKey, locale: 'en');
        $this->setAttr($parentId, 5, 'boolean', $group['new'] ?? 0);
        $this->setAttr($parentId, 6, 'boolean', $group['featured'] ?? 0);
        $this->setAttr($parentId, 7, 'boolean', 1);
        $this->setAttr($parentId, 8, 'boolean', 1, channel: 'default');
        $this->setAttr($parentId, 9, 'textarea', $group['short_description'] ?? $group['name'], locale: 'en');
        $this->setAttr($parentId, 10, 'textarea', $group['description'] ?? '<p>' . $group['name'] . '</p>', locale: 'en');
        $this->setAttr($parentId, 11, 'price', $minPrice);
        $this->setAttr($parentId, 22, 'text', $group['weight'] ?? '0.2');

        if (isset($group['brand'], $this->brandOptions[$group['brand']])) {
            $this->setAttr($parentId, 25, 'select', $this->brandOptions[$group['brand']]);
        }

        DB::table('product_channels')->insert(['product_id' => $parentId, 'channel_id' => 1]);

        $catRows = [['product_id' => $parentId, 'category_id' => 1]];
        if (isset($group['category'], $this->categoryIds[$group['category']])) {
            $catRows[] = ['product_id' => $parentId, 'category_id' => $this->categoryIds[$group['category']]];
        }
        DB::table('product_categories')->insert($catRows);

        // Determine which attributes vary across variants
        $hasRam     = count(array_unique(array_filter(array_column($group['variants'], 'ram')))) > 1;
        $hasStorage = count(array_unique(array_filter(array_column($group['variants'], 'storage')))) > 1;

        if ($hasRam && $this->ramAttrId) {
            DB::table('product_super_attributes')->insert(['product_id' => $parentId, 'attribute_id' => $this->ramAttrId]);
        }
        if ($hasStorage && $this->storageAttrId) {
            DB::table('product_super_attributes')->insert(['product_id' => $parentId, 'attribute_id' => $this->storageAttrId]);
        }
        if (! $hasRam && ! $hasStorage && $this->storageAttrId) {
            DB::table('product_super_attributes')->insert(['product_id' => $parentId, 'attribute_id' => $this->storageAttrId]);
        }

        foreach ($group['variants'] as $variant) {
            $this->createChildProduct($parentId, $group, $variant);
        }

        $this->downloadProductImage($parentId, $group['name'], $group['brand'] ?? '', $group['category'] ?? '');
    }

    protected function createChildProduct(int $parentId, array $group, array $variant): void
    {
        $ram     = $variant['ram'] ?? '';
        $storage = $variant['storage'] ?? '';
        $parts   = array_filter([$ram, $storage]);
        $spec    = implode('/', $parts);
        $name    = $group['name'] . ($spec ? ' ' . $spec : '');
        $sku     = strtoupper(Str::slug($name, '-'));

        if (DB::table('products')->where('sku', $sku)->exists()) {
            return;
        }

        $childId = DB::table('products')->insertGetId([
            'type'                => 'simple',
            'sku'                 => $sku,
            'attribute_family_id' => 1,
            'parent_id'           => $parentId,
            'created_at'          => $this->timestamp,
            'updated_at'          => $this->timestamp,
        ]);

        $this->setAttr($childId, 1, 'text', $sku);
        $this->setAttr($childId, 2, 'text', $name, locale: 'en');
        $this->setAttr($childId, 3, 'text', Str::slug($name), locale: 'en');
        $this->setAttr($childId, 7, 'boolean', 0);
        $this->setAttr($childId, 8, 'boolean', 1, channel: 'default');
        $this->setAttr($childId, 11, 'price', $variant['price']);
        $this->setAttr($childId, 22, 'text', $group['weight'] ?? '0.2');

        if ($ram && isset($this->ramOptions[$ram]) && $this->ramAttrId) {
            $this->setAttr($childId, $this->ramAttrId, 'select', $this->ramOptions[$ram]);
        }
        if ($storage && isset($this->storageOptions[$storage]) && $this->storageAttrId) {
            $this->setAttr($childId, $this->storageAttrId, 'select', $this->storageOptions[$storage]);
        }

        DB::table('product_inventories')->insert([
            'qty'                 => 100,
            'product_id'          => $childId,
            'inventory_source_id' => 1,
            'vendor_id'           => 0,
        ]);

        $this->downloadProductImage($childId, $group['name'], $group['brand'] ?? '', $group['category'] ?? '');
    }

    // =========================================================================
    // Simple Product
    // =========================================================================

    protected function createSimpleProduct(array $def): void
    {
        $sku = strtoupper(Str::slug($def['name'], '-'));

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

        $urlKey = Str::slug($def['name']);

        $this->setAttr($productId, 1, 'text', $sku);
        $this->setAttr($productId, 2, 'text', $def['name'], locale: 'en');
        $this->setAttr($productId, 3, 'text', $urlKey, locale: 'en');
        $this->setAttr($productId, 5, 'boolean', $def['new'] ?? 0);
        $this->setAttr($productId, 6, 'boolean', $def['featured'] ?? 0);
        $this->setAttr($productId, 7, 'boolean', 1);
        $this->setAttr($productId, 8, 'boolean', 1, channel: 'default');
        $this->setAttr($productId, 9, 'textarea', $def['short_description'] ?? $def['name'], locale: 'en');
        $this->setAttr($productId, 10, 'textarea', $def['description'] ?? '<p>' . $def['name'] . '</p>', locale: 'en');
        $this->setAttr($productId, 11, 'price', $def['price']);
        $this->setAttr($productId, 22, 'text', $def['weight'] ?? '0.2');

        if (isset($def['special_price']) && $def['special_price'] > 0) {
            $this->setAttr($productId, 13, 'price', $def['special_price']);
        }

        if (isset($def['brand'], $this->brandOptions[$def['brand']])) {
            $this->setAttr($productId, 25, 'select', $this->brandOptions[$def['brand']]);
        }

        DB::table('product_channels')->insert(['product_id' => $productId, 'channel_id' => 1]);

        $catRows = [['product_id' => $productId, 'category_id' => 1]];
        if (isset($def['category'], $this->categoryIds[$def['category']])) {
            $catRows[] = ['product_id' => $productId, 'category_id' => $this->categoryIds[$def['category']]];
        }
        DB::table('product_categories')->insert($catRows);

        DB::table('product_inventories')->insert([
            'qty'                 => $def['qty'] ?? 100,
            'product_id'          => $productId,
            'inventory_source_id' => 1,
            'vendor_id'           => 0,
        ]);

        $this->downloadProductImage($productId, $def['name'], $def['brand'] ?? '', $def['category'] ?? '');
    }

    // =========================================================================
    // Attribute Value Helper
    // =========================================================================

    protected function setAttr(
        int $productId,
        int $attributeId,
        string $type,
        mixed $value,
        ?string $channel = null,
        ?string $locale = null,
    ): void {
        $uniqueId    = implode('|', [$channel ?? '', $locale ?? '', $productId, $attributeId]);
        $valueColumn = $this->typeColumnMap[$type] ?? 'text_value';

        DB::table('product_attribute_values')->insert(array_merge($this->nullColumns, [
            'attribute_id' => $attributeId,
            'product_id'   => $productId,
            'channel'      => $channel,
            'locale'       => $locale,
            'unique_id'    => $uniqueId,
            $valueColumn   => $value,
        ]));
    }

    // =========================================================================
    // Product Image Download
    // =========================================================================

    protected function downloadProductImage(int $productId, string $name, string $brand = '', string $category = ''): void
    {
        try {
            $imageUrl = $this->getProductImageUrl($name, $brand, $category);

            $context = stream_context_create([
                'http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0 (compatible; Phonix/1.0)'],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);

            $imageContent = @file_get_contents($imageUrl, false, $context);

            if ($imageContent === false || strlen($imageContent) < 1000) {
                return;
            }

            $path = 'product/' . $productId . '/' . Str::random(20) . '.jpg';
            Storage::disk('public')->put($path, $imageContent);

            DB::table('product_images')->insert([
                'type'       => 'images',
                'path'       => $path,
                'product_id' => $productId,
                'position'   => 1,
            ]);
        } catch (\Throwable $e) {
            // Product created without image — acceptable
        }
    }

    protected function getProductImageUrl(string $name, string $brand = '', string $category = ''): string
    {
        $lower = strtolower($name . ' ' . $brand . ' ' . $category);

        // ── Apple ────────────────────────────────────────────────────────────
        if (str_contains($lower, 'iphone 17 pro max')) {
            return 'https://images.unsplash.com/photo-1591337676887-a217a8b797a1?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone 17 pro') || str_contains($lower, 'iphone 15 pro')) {
            return 'https://images.unsplash.com/photo-1632661674596-df8be070a5c5?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone 17 air')) {
            return 'https://images.unsplash.com/photo-1611791484670-ce19b801d192?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone 17') || str_contains($lower, 'iphone 16')) {
            return 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone')) {
            return 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'airpods pro')) {
            return 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'airpods')) {
            return 'https://images.unsplash.com/photo-1588423771073-b8903febb85d?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'airtag')) {
            return 'https://images.unsplash.com/photo-1614624532983-4ce6d0a7c1de?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'pencil')) {
            return 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=900&h=900&fit=crop&q=90';
        }

        // ── Samsung accessories ──────────────────────────────────────────────
        if (str_contains($lower, 'buds')) {
            return 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'galaxy watch')) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=900&h=900&fit=crop&q=90';
        }

        // ── Samsung tablets ──────────────────────────────────────────────────
        if (str_contains($lower, 'tab s')) {
            return 'https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'tab a') || str_contains($lower, ' tab ') || str_contains($lower, 'tab"')) {
            return 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=900&h=900&fit=crop&q=90';
        }

        // ── Samsung Galaxy phones — use Samsung-specific images only ─────────
        if (str_contains($lower, 'galaxy s') || str_contains($lower, 'galaxy a5') || str_contains($lower, 'galaxy a36') || str_contains($lower, 'galaxy a56')) {
            return 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'galaxy') || str_contains($lower, 'samsung')) {
            return 'https://images.unsplash.com/photo-1567581935884-3349723552ca?w=900&h=900&fit=crop&q=90';
        }

        // ── Xiaomi / Redmi ───────────────────────────────────────────────────
        if (str_contains($lower, 'redmi note') || str_contains($lower, 'note 13') || str_contains($lower, 'note 14')) {
            return 'https://images.unsplash.com/photo-1619406060869-96a6c7bd024e?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'xiaomi') || str_contains($lower, 'redmi')) {
            return 'https://images.unsplash.com/photo-1599669454699-248893623440?w=900&h=900&fit=crop&q=90';
        }

        // ── Tecno ────────────────────────────────────────────────────────────
        if (str_contains($lower, 'camon')) {
            return 'https://images.unsplash.com/photo-1598327106026-535f1a5c0d6f?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'tecno') || str_contains($lower, 'spark') || str_contains($lower, 'pova')) {
            return 'https://images.unsplash.com/photo-1546869846-e3c0bf6d1e4e?w=900&h=900&fit=crop&q=90';
        }

        // ── Non-Samsung tablets ──────────────────────────────────────────────
        if ($category === 'Tablets' || str_contains($lower, 'teclast') || str_contains($lower, 'doogee')) {
            return 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=900&h=900&fit=crop&q=90';
        }

        // ── Gaming ───────────────────────────────────────────────────────────
        if (str_contains($lower, 'ps5') || str_contains($lower, 'playstation')) {
            return 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=900&h=900&fit=crop&q=90';
        }

        return 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=900&h=900&fit=crop&q=90';
    }

    // =========================================================================
    // Product Group Definitions
    // =========================================================================

    protected function getSamsungGroups(): array
    {
        $base = ['brand' => 'Samsung', 'category' => 'Samsung Phones', 'weight' => '0.2'];

        return [
            array_merge($base, [
                'name' => 'Samsung Galaxy A06 5G', 'new' => 0, 'featured' => 0,
                'short_description' => 'Samsung Galaxy A06 5G 4/128GB. Entry-level 5G connectivity.',
                'description'       => '<p>The Samsung Galaxy A06 5G delivers 5G connectivity at an accessible price. Great for everyday communication and browsing with reliable Samsung quality.</p>',
                'variants'          => [['ram' => '4GB', 'storage' => '128GB', 'price' => 94]],
            ]),
            array_merge($base, [
                'name' => 'Samsung Galaxy A07 4G', 'new' => 0, 'featured' => 0,
                'short_description' => 'Samsung Galaxy A07 4G. Available in 64GB and 128GB storage.',
                'description'       => '<p>The Samsung Galaxy A07 4G offers a large 6.5" display, capable camera, and all-day battery life. Choose between 64GB and 128GB storage options.</p>',
                'variants' => [
                    ['ram' => '4GB', 'storage' => '64GB', 'price' => 95],
                    ['ram' => '4GB', 'storage' => '128GB', 'price' => 105],
                ],
            ]),
            array_merge($base, [
                'name' => 'Samsung Galaxy A16', 'new' => 0, 'featured' => 1,
                'short_description' => 'Samsung Galaxy A16 8/256GB. Mid-range excellence.',
                'description'       => '<p>The Samsung Galaxy A16 combines a stunning display with smooth performance. Features 8GB RAM and 256GB storage for all your apps, photos, and media.</p>',
                'variants'          => [['ram' => '8GB', 'storage' => '256GB', 'price' => 180]],
            ]),
            array_merge($base, [
                'name' => 'Samsung Galaxy A17', 'new' => 1, 'featured' => 1,
                'short_description' => 'Samsung Galaxy A17. Choose your RAM and storage configuration.',
                'description'       => '<p>The Samsung Galaxy A17 is a versatile mid-range phone with a vibrant Super AMOLED display, multi-lens camera, and long battery life. Available in 4GB/128GB, 6GB/128GB, and 8GB/256GB configurations.</p>',
                'variants' => [
                    ['ram' => '4GB', 'storage' => '128GB', 'price' => 152],
                    ['ram' => '6GB', 'storage' => '128GB', 'price' => 168],
                    ['ram' => '8GB', 'storage' => '256GB', 'price' => 200],
                ],
            ]),
            array_merge($base, [
                'name' => 'Samsung Galaxy A26', 'new' => 1, 'featured' => 1,
                'short_description' => 'Samsung Galaxy A26. Next-gen mid-range with 5G.',
                'description'       => '<p>The Samsung Galaxy A26 brings 5G performance to the mid-range segment with an advanced camera system and Samsung\'s smooth One UI experience. Available in 6GB/128GB and 8GB/256GB.</p>',
                'variants' => [
                    ['ram' => '6GB', 'storage' => '128GB', 'price' => 182],
                    ['ram' => '8GB', 'storage' => '256GB', 'price' => 238],
                ],
            ]),
            array_merge($base, [
                'name' => 'Samsung Galaxy A36', 'new' => 1, 'featured' => 1,
                'short_description' => 'Samsung Galaxy A36 8/256GB. Premium mid-range performance.',
                'description'       => '<p>The Samsung Galaxy A36 delivers premium features in a mid-range package. Advanced cameras, 8GB RAM, 256GB storage, and Samsung\'s flagship design language.</p>',
                'variants'          => [['ram' => '8GB', 'storage' => '256GB', 'price' => 288]],
            ]),
            array_merge($base, [
                'name' => 'Samsung Galaxy A56', 'new' => 1, 'featured' => 1,
                'short_description' => 'Samsung Galaxy A56. Top-tier A-series in 128GB or 256GB.',
                'description'       => '<p>The Samsung Galaxy A56 is Samsung\'s most advanced A-series phone with a premium camera system, powerful processor, and stunning display. Choose 128GB or 256GB storage.</p>',
                'variants' => [
                    ['ram' => '8GB', 'storage' => '128GB', 'price' => 320],
                    ['ram' => '8GB', 'storage' => '256GB', 'price' => 377],
                ],
            ]),
        ];
    }

    protected function getTecnoGroups(): array
    {
        $base = ['brand' => 'Tecno', 'category' => 'Tecno Phones', 'weight' => '0.2'];

        return [
            array_merge($base, [
                'name' => 'Tecno Spark Go One', 'new' => 0, 'featured' => 0,
                'short_description' => 'Tecno Spark Go One. Affordable in 3/64GB or 4/128GB.',
                'description'       => '<p>The Tecno Spark Go One offers an accessible entry point to smartphones with a large display and reliable performance. Available in two configurations.</p>',
                'variants' => [
                    ['ram' => '3GB', 'storage' => '64GB', 'price' => 71],
                    ['ram' => '4GB', 'storage' => '128GB', 'price' => 81],
                ],
            ]),
            array_merge($base, [
                'name' => 'Tecno Spark Go 2', 'new' => 0, 'featured' => 0,
                'short_description' => 'Tecno Spark Go 2 3/64GB. Budget-friendly daily driver.',
                'description'       => '<p>The Tecno Spark Go 2 delivers essential smartphone features at an unbeatable price with a great display and all-day battery.</p>',
                'variants' => [['ram' => '3GB', 'storage' => '64GB', 'price' => 71]],
            ]),
            array_merge($base, [
                'name' => 'Tecno Spark Go 3', 'new' => 0, 'featured' => 0,
                'short_description' => 'Tecno Spark Go 3 4/64GB. Upgraded entry-level performance.',
                'description'       => '<p>The Tecno Spark Go 3 steps up with improved performance and camera capabilities at an affordable price.</p>',
                'variants' => [['ram' => '4GB', 'storage' => '64GB', 'price' => 86]],
            ]),
            array_merge($base, [
                'name' => 'Tecno Spark 4C', 'new' => 0, 'featured' => 1,
                'short_description' => 'Tecno Spark 4C 8/256GB. Feature-rich mid-range phone.',
                'description'       => '<p>The Tecno Spark 4C packs 8GB RAM and 256GB storage into a sleek design with a high-refresh display and impressive camera system.</p>',
                'variants' => [['ram' => '8GB', 'storage' => '256GB', 'price' => 111]],
            ]),
            array_merge($base, [
                'name' => 'Tecno Spark 20 Pro', 'new' => 0, 'featured' => 1,
                'short_description' => 'Tecno Spark 20 Pro 12/256GB. Pro-level Tecno experience.',
                'description'       => '<p>The Tecno Spark 20 Pro delivers a premium experience with 12GB RAM, 256GB storage, and an impressive camera setup for the price.</p>',
                'variants' => [['ram' => '12GB', 'storage' => '256GB', 'price' => 149]],
            ]),
            array_merge($base, [
                'name' => 'Tecno Spark Slim', 'new' => 1, 'featured' => 1,
                'short_description' => 'Tecno Spark Slim 8/256GB. Ultra-thin design meets performance.',
                'description'       => '<p>The Tecno Spark Slim combines an ultra-slim profile with excellent performance — 8GB RAM, 256GB storage, and a stunning display in a thin form factor.</p>',
                'variants' => [['ram' => '8GB', 'storage' => '256GB', 'price' => 190]],
            ]),
            array_merge($base, [
                'name' => 'Tecno Pova 7', 'new' => 0, 'featured' => 1,
                'short_description' => 'Tecno Pova 7 8/256GB. Gaming-focused powerhouse.',
                'description'       => '<p>The Tecno Pova 7 is built for performance with a massive battery, gaming-tuned processor, 8GB RAM, and 256GB storage for power users.</p>',
                'variants' => [['ram' => '8GB', 'storage' => '256GB', 'price' => 160]],
            ]),
            array_merge($base, [
                'name' => 'Tecno Pova 7 5G', 'new' => 1, 'featured' => 1,
                'short_description' => 'Tecno Pova 7 5G 8/256GB. 5G gaming smartphone.',
                'description'       => '<p>The Tecno Pova 7 5G brings 5G connectivity to the gaming arena with a powerful processor, 8GB RAM, and 256GB storage.</p>',
                'variants' => [['ram' => '8GB', 'storage' => '256GB', 'price' => 217]],
            ]),
            array_merge($base, [
                'name' => 'Tecno Camon 20 Premier', 'new' => 1, 'featured' => 1,
                'short_description' => 'Tecno Camon 20 Premier 8/512GB. Photography flagship.',
                'description'       => '<p>The Tecno Camon 20 Premier is the ultimate photography smartphone from Tecno with an advanced camera system, 8GB RAM, and massive 512GB storage.</p>',
                'variants' => [['ram' => '8GB', 'storage' => '512GB', 'price' => 325]],
            ]),
            array_merge($base, [
                'name' => 'Tecno Camon 40 Pro 5G', 'new' => 1, 'featured' => 1,
                'short_description' => 'Tecno Camon 40 Pro 5G 12/256GB. Next-gen camera phone.',
                'description'       => '<p>The Tecno Camon 40 Pro 5G redefines mobile photography with AI cameras, 12GB RAM, 256GB storage, and 5G speed.</p>',
                'variants' => [['ram' => '12GB', 'storage' => '256GB', 'price' => 291]],
            ]),
        ];
    }

    protected function getAppleGroups(): array
    {
        $base = ['brand' => 'Apple', 'category' => 'Apple iPhones', 'weight' => '0.2', 'featured' => 1];

        return [
            array_merge($base, [
                'name' => 'Apple iPhone 15 Pro', 'new' => 0,
                'short_description' => 'Apple iPhone 15 Pro 256GB. Titanium design, A17 Pro chip.',
                'description'       => '<p>The Apple iPhone 15 Pro features titanium design, A17 Pro chip, and a pro camera system with 48MP main sensor.</p>',
                'variants' => [['ram' => '', 'storage' => '256GB', 'price' => 1100]],
            ]),
            array_merge($base, [
                'name' => 'Apple iPhone 16', 'new' => 0,
                'short_description' => 'Apple iPhone 16 128GB. A18 chip, Camera Control button.',
                'description'       => '<p>The Apple iPhone 16 features the A18 chip, Camera Control, and Action button — a significant upgrade for everyday users.</p>',
                'variants' => [['ram' => '', 'storage' => '128GB', 'price' => 740]],
            ]),
            array_merge($base, [
                'name' => 'Apple iPhone 17', 'new' => 1,
                'short_description' => 'Apple iPhone 17 256GB. Latest iPhone with next-gen performance.',
                'description'       => '<p>The Apple iPhone 17 brings the next generation of iPhone experience with Apple\'s latest chip, improved cameras, and refined design.</p>',
                'variants' => [['ram' => '', 'storage' => '256GB', 'price' => 930]],
            ]),
            array_merge($base, [
                'name' => 'Apple iPhone 17 Air', 'new' => 1,
                'short_description' => 'Apple iPhone 17 Air. Apple\'s thinnest iPhone in 256GB or 512GB.',
                'description'       => '<p>The Apple iPhone 17 Air combines an incredibly slim profile with powerful performance and an excellent camera system. Available in 256GB and 512GB.</p>',
                'variants' => [
                    ['ram' => '', 'storage' => '256GB', 'price' => 1030],
                    ['ram' => '', 'storage' => '512GB', 'price' => 1240],
                ],
            ]),
            array_merge($base, [
                'name' => 'Apple iPhone 17 Pro', 'new' => 1,
                'short_description' => 'Apple iPhone 17 Pro. Pro camera system, A19 Pro chip.',
                'description'       => '<p>The Apple iPhone 17 Pro features Apple\'s most advanced camera system with 48MP periscope telephoto, titanium design, and A19 Pro chip. Available in 256GB and 512GB.</p>',
                'variants' => [
                    ['ram' => '', 'storage' => '256GB', 'price' => 1400],
                    ['ram' => '', 'storage' => '512GB', 'price' => 1580],
                ],
            ]),
            array_merge($base, [
                'name' => 'Apple iPhone 17 Pro Max', 'new' => 1,
                'short_description' => 'Apple iPhone 17 Pro Max. The ultimate iPhone up to 2TB.',
                'description'       => '<p>The Apple iPhone 17 Pro Max is the pinnacle of iPhone technology — largest display, most advanced cameras, longest battery life. Available from 256GB up to 2TB storage.</p>',
                'variants' => [
                    ['ram' => '', 'storage' => '256GB', 'price' => 1520],
                    ['ram' => '', 'storage' => '512GB', 'price' => 1750],
                    ['ram' => '', 'storage' => '1TB',   'price' => 2000],
                    ['ram' => '', 'storage' => '2TB',   'price' => 2200],
                ],
            ]),
        ];
    }

    protected function getXiaomiGroups(): array
    {
        $base = ['brand' => 'Xiaomi', 'category' => 'Xiaomi Phones', 'weight' => '0.2'];

        return [
            array_merge($base, [
                'name' => 'Xiaomi Redmi A3', 'new' => 0, 'featured' => 0,
                'short_description' => 'Xiaomi Redmi A3 3/64GB. Essential smartphone at a great price.',
                'description'       => '<p>The Xiaomi Redmi A3 delivers a clean Android experience at the most accessible price point with a large display and reliable battery life.</p>',
                'variants' => [['ram' => '3GB', 'storage' => '64GB', 'price' => 63]],
            ]),
            array_merge($base, [
                'name' => 'Xiaomi Redmi A5', 'new' => 0, 'featured' => 0,
                'short_description' => 'Xiaomi Redmi A5. Available in 3/64GB or 4/128GB.',
                'description'       => '<p>The Xiaomi Redmi A5 offers a solid everyday smartphone experience with a capable camera and all-day battery. Choose your storage configuration.</p>',
                'variants' => [
                    ['ram' => '3GB', 'storage' => '64GB',  'price' => 75],
                    ['ram' => '4GB', 'storage' => '128GB', 'price' => 89],
                ],
            ]),
            array_merge($base, [
                'name' => 'Xiaomi Redmi 15C', 'new' => 0, 'featured' => 1,
                'short_description' => 'Xiaomi Redmi 15C. Feature-packed in 4/128GB or 6/128GB.',
                'description'       => '<p>The Xiaomi Redmi 15C steps up with a superior camera and smoother performance. Choose 4GB or 6GB RAM to match your needs.</p>',
                'variants' => [
                    ['ram' => '4GB', 'storage' => '128GB', 'price' => 112],
                    ['ram' => '6GB', 'storage' => '128GB', 'price' => 135],
                ],
            ]),
            array_merge($base, [
                'name' => 'Xiaomi Redmi Note 14 4G', 'new' => 1, 'featured' => 1,
                'short_description' => 'Xiaomi Redmi Note 14 4G 8/256GB. Note series excellence.',
                'description'       => '<p>The Xiaomi Redmi Note 14 4G delivers Note series performance with 8GB RAM, 256GB storage, and an advanced camera for photography enthusiasts.</p>',
                'variants' => [['ram' => '8GB', 'storage' => '256GB', 'price' => 178]],
            ]),
            array_merge($base, [
                'name' => 'Xiaomi Redmi Note 13 Pro 5G', 'new' => 1, 'featured' => 1,
                'short_description' => 'Xiaomi Redmi Note 13 Pro 5G 12/512GB. 200MP camera, 5G speed.',
                'description'       => '<p>The Xiaomi Redmi Note 13 Pro 5G is the ultimate Note smartphone with a 200MP camera, 12GB RAM, 512GB storage, and 5G connectivity.</p>',
                'variants' => [['ram' => '12GB', 'storage' => '512GB', 'price' => 280]],
            ]),
        ];
    }

    protected function getTabletGroups(): array
    {
        return [
            [
                'name' => 'Samsung Galaxy Tab A11 8.7"', 'brand' => 'Samsung', 'category' => 'Tablets', 'weight' => '0.5',
                'new' => 0, 'featured' => 1,
                'short_description' => 'Samsung Galaxy Tab A11 8.7" WiFi 4/64GB. Compact Android tablet.',
                'description'       => '<p>The Samsung Galaxy Tab A11 8.7" is a compact, portable tablet perfect for media consumption, reading, and light productivity.</p>',
                'variants' => [['ram' => '4GB', 'storage' => '64GB', 'price' => 113]],
            ],
            [
                'name' => 'Samsung Galaxy Tab A11 10.9"', 'brand' => 'Samsung', 'category' => 'Tablets', 'weight' => '0.5',
                'new' => 1, 'featured' => 1,
                'short_description' => 'Samsung Galaxy Tab A11 10.9" WiFi 6/128GB. Larger display tablet.',
                'description'       => '<p>The Samsung Galaxy Tab A11 10.9" offers a larger display for productivity and entertainment with 6GB RAM and 128GB storage in a slim profile.</p>',
                'variants' => [['ram' => '6GB', 'storage' => '128GB', 'price' => 211]],
            ],
            [
                'name' => 'Samsung Galaxy Tab S10 Plus', 'brand' => 'Samsung', 'category' => 'Tablets', 'weight' => '0.6',
                'new' => 1, 'featured' => 1,
                'short_description' => 'Samsung Galaxy Tab S10 Plus WiFi 12/256GB. Premium flagship tablet.',
                'description'       => '<p>The Samsung Galaxy Tab S10 Plus is Samsung\'s flagship tablet — stunning AMOLED display, 12GB RAM, 256GB storage, and S Pen support for professional creativity.</p>',
                'variants' => [['ram' => '12GB', 'storage' => '256GB', 'price' => 800]],
            ],
            [
                'name' => 'Doogee U10 10" WiFi', 'brand' => 'Doogee', 'category' => 'Tablets', 'weight' => '0.5',
                'new' => 0, 'featured' => 0,
                'short_description' => 'Doogee U10 10" WiFi 4/128GB. Affordable Android tablet.',
                'description'       => '<p>The Doogee U10 10" tablet offers great value with a large display, 4GB RAM, and 128GB storage for budget-conscious buyers.</p>',
                'variants' => [['ram' => '4GB', 'storage' => '128GB', 'price' => 70]],
            ],
            [
                'name' => 'Teclast P30T 10" WiFi', 'brand' => 'Teclast', 'category' => 'Tablets', 'weight' => '0.5',
                'new' => 0, 'featured' => 0,
                'short_description' => 'Teclast P30T 10" WiFi 4/64GB. Budget productivity tablet.',
                'description'       => '<p>The Teclast P30T is a capable budget tablet with a 10" display, Android OS, and 4GB RAM — ideal for students and casual users.</p>',
                'variants' => [['ram' => '4GB', 'storage' => '64GB', 'price' => 89]],
            ],
        ];
    }

    protected function getAccessories(): array
    {
        $base = ['category' => 'Accessories', 'weight' => '0.05', 'qty' => 200];

        return [
            array_merge($base, [
                'name' => 'Samsung Galaxy Buds Core', 'brand' => 'Samsung', 'price' => 35,
                'new' => 0, 'featured' => 0,
                'short_description' => 'Samsung Galaxy Buds Core. True wireless earbuds.',
                'description'       => '<p>Samsung Galaxy Buds Core deliver clear, rich audio in a compact true wireless design with easy pairing to Samsung devices.</p>',
            ]),
            array_merge($base, [
                'name' => 'Samsung Galaxy Buds 3 Pro', 'brand' => 'Samsung', 'price' => 169,
                'new' => 1, 'featured' => 1,
                'short_description' => 'Samsung Galaxy Buds 3 Pro with ANC. Premium audio.',
                'description'       => '<p>The Samsung Galaxy Buds 3 Pro features Intelligent ANC, Hi-Fi audio with a 2-way speaker system, and premium build quality.</p>',
            ]),
            array_merge($base, [
                'name' => 'Samsung Galaxy Watch 7 Ultra 47mm', 'brand' => 'Samsung', 'price' => 305, 'weight' => '0.1',
                'new' => 1, 'featured' => 1,
                'short_description' => 'Samsung Galaxy Watch 7 Ultra 47mm. Premium smartwatch.',
                'description'       => '<p>The Samsung Galaxy Watch 7 Ultra is Samsung\'s most capable smartwatch with advanced health monitoring, titanium build, and a 47mm display.</p>',
            ]),
            array_merge($base, [
                'name' => 'Apple AirPods 3rd Gen', 'brand' => 'Apple', 'price' => 102,
                'new' => 0, 'featured' => 1,
                'short_description' => 'Apple AirPods 3rd Generation. Spatial audio, all-day battery.',
                'description'       => '<p>Apple AirPods (3rd Gen) deliver immersive Spatial Audio with dynamic head tracking, adaptive EQ, and all-day battery life.</p>',
            ]),
            array_merge($base, [
                'name' => 'Apple AirPods Pro 2 ANC', 'brand' => 'Apple', 'price' => 148,
                'new' => 0, 'featured' => 1,
                'short_description' => 'Apple AirPods Pro 2 with Active Noise Cancellation.',
                'description'       => '<p>Apple AirPods Pro 2 feature 2x more Active Noise Cancellation, Adaptive Transparency, and Personalized Spatial Audio.</p>',
            ]),
            array_merge($base, [
                'name' => 'Apple AirTag 4-Pack', 'brand' => 'Apple', 'price' => 78, 'weight' => '0.1',
                'new' => 0, 'featured' => 0,
                'short_description' => 'Apple AirTag 4-Pack. Find your items effortlessly.',
                'description'       => '<p>Apple AirTag 4-Pack helps you track important items with Ultra Wideband Precision Finding and the entire Find My network.</p>',
            ]),
            array_merge($base, [
                'name' => 'Apple Pencil 2nd Gen', 'brand' => 'Apple', 'price' => 79,
                'new' => 0, 'featured' => 0,
                'short_description' => 'Apple Pencil 2nd Generation. Pixel-perfect precision for iPad.',
                'description'       => '<p>Apple Pencil (2nd gen) attaches magnetically to supported iPad models and charges wirelessly. Features tilt sensitivity and pixel-perfect precision.</p>',
            ]),
        ];
    }

    protected function getGamingProducts(): array
    {
        return [
            [
                'name' => 'Sony PlayStation 5 1TB', 'brand' => 'Sony', 'category' => 'Gaming',
                'price' => 600, 'weight' => '4.5', 'new' => 1, 'featured' => 1, 'qty' => 20,
                'short_description' => 'Sony PlayStation 5 1TB SSD. Next-gen gaming console.',
                'description'       => '<p>The Sony PlayStation 5 (1TB) delivers breathtaking gaming with lightning-fast SSD loading, 4K ray-traced graphics, and the immersive DualSense controller. The ultimate gaming console.</p>',
            ],
        ];
    }

    // =========================================================================
    // Flat Indexer
    // =========================================================================

    protected function reindexFlat(): void
    {
        if (class_exists(\Webkul\Product\Helpers\Indexers\Flat::class)) {
            $indexer  = app(\Webkul\Product\Helpers\Indexers\Flat::class);
            $products = \Webkul\Product\Models\Product::whereNull('parent_id')->get();

            foreach ($products->chunk(50) as $chunk) {
                $indexer->reindexBatch($chunk);
            }
        }
    }
}
