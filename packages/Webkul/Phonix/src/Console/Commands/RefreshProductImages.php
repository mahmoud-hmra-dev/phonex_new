<?php

namespace Webkul\Phonix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RefreshProductImages extends Command
{
    protected $signature = 'phonix:refresh-images';

    protected $description = 'Delete low-res product images and re-download high-quality versions';

    public function handle(): int
    {
        $products = DB::table('products')
            ->select('products.id', 'products.sku')
            ->whereIn('products.type', ['simple', 'configurable'])
            ->get();

        if ($products->isEmpty()) {
            $this->warn('No products found.');
            return self::SUCCESS;
        }

        $this->info("Found {$products->count()} products. Refreshing images...");
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $this->refreshProductImage($product->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('All product images refreshed successfully!');
        return self::SUCCESS;
    }

    protected function refreshProductImage(int $productId): void
    {
        // Get product name and category from attribute values
        $nameRow = DB::table('product_attribute_values')
            ->where('product_id', $productId)
            ->where('attribute_id', 2)
            ->where('locale', 'en')
            ->first();

        $name = $nameRow->text_value ?? '';

        // Get category
        $categoryId = DB::table('product_categories')
            ->where('product_id', $productId)
            ->where('category_id', '!=', 1)
            ->value('category_id');

        $categoryName = '';
        if ($categoryId) {
            $catTranslation = DB::table('category_translations')
                ->where('category_id', $categoryId)
                ->where('locale', 'en')
                ->first();
            $categoryName = $catTranslation->name ?? '';
        }

        // Delete existing images from storage
        $existingImages = DB::table('product_images')
            ->where('product_id', $productId)
            ->get();

        foreach ($existingImages as $img) {
            if ($img->path && Storage::disk('public')->exists($img->path)) {
                Storage::disk('public')->delete($img->path);
            }
        }

        // Delete image directory
        $dir = 'product/' . $productId;
        if (Storage::disk('public')->exists($dir)) {
            try {
                Storage::disk('public')->deleteDirectory($dir);
            } catch (\Throwable $e) {
                // continue
            }
        }

        // Delete DB records
        DB::table('product_images')->where('product_id', $productId)->delete();

        if (empty($name)) {
            return;
        }

        // Download new high-quality image
        $imageUrl = $this->getProductImageUrl($name, '', $categoryName);

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'    => 15,
                    'user_agent' => 'Mozilla/5.0 (compatible; Phonix/1.0)',
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $imageContent = @file_get_contents($imageUrl, false, $context);

            if ($imageContent === false || strlen($imageContent) < 1000) {
                return;
            }

            $filename = Str::random(20) . '.jpg';
            $path     = 'product/' . $productId . '/' . $filename;

            Storage::disk('public')->put($path, $imageContent);

            DB::table('product_images')->insert([
                'type'       => 'images',
                'path'       => $path,
                'product_id' => $productId,
                'position'   => 1,
            ]);
        } catch (\Throwable $e) {
            // Skip on failure
        }
    }

    protected function getProductImageUrl(string $name, string $brand = '', string $category = ''): string
    {
        $lower = strtolower($name . ' ' . $brand . ' ' . $category);

        if (str_contains($lower, 'iphone 17 pro max')) {
            return 'https://images.unsplash.com/photo-1591337676887-a217a8b797a1?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone 17 pro') || str_contains($lower, 'iphone 15 pro')) {
            return 'https://images.unsplash.com/photo-1632661674596-df8be070a5c5?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone 17 air')) {
            return 'https://images.unsplash.com/photo-1611791484670-ce19b801d192?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone 17')) {
            return 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone 16')) {
            return 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'iphone')) {
            return 'https://images.unsplash.com/photo-1574755393849-623942496936?w=900&h=900&fit=crop&q=90';
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
        if (str_contains($lower, 'buds')) {
            return 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'galaxy watch')) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'tab s10') || str_contains($lower, 'tab s')) {
            return 'https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'tab a') || str_contains($lower, 'tab')) {
            return 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'galaxy a5') || str_contains($lower, 'galaxy a3')) {
            return 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'galaxy a1') || str_contains($lower, 'galaxy a2')) {
            return 'https://images.unsplash.com/photo-1574755393849-623942496936?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'samsung') || str_contains($lower, 'galaxy')) {
            return 'https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'redmi note') || str_contains($lower, 'note 13') || str_contains($lower, 'note 14')) {
            return 'https://images.unsplash.com/photo-1619406060869-96a6c7bd024e?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'xiaomi') || str_contains($lower, 'redmi')) {
            return 'https://images.unsplash.com/photo-1599669454699-248893623440?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'camon')) {
            return 'https://images.unsplash.com/photo-1598327106026-535f1a5c0d6f?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'tecno') || str_contains($lower, 'spark') || str_contains($lower, 'pova')) {
            return 'https://images.unsplash.com/photo-1546869846-e3c0bf6d1e4e?w=900&h=900&fit=crop&q=90';
        }
        if ($category === 'Tablets' || str_contains($lower, 'tablet') || str_contains($lower, 'teclast') || str_contains($lower, 'doogee')) {
            return 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=900&h=900&fit=crop&q=90';
        }
        if (str_contains($lower, 'ps5') || str_contains($lower, 'playstation')) {
            return 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=900&h=900&fit=crop&q=90';
        }

        return 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=900&h=900&fit=crop&q=90';
    }
}
