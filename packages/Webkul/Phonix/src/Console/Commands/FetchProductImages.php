<?php

namespace Webkul\Phonix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchProductImages extends Command
{
    protected $signature = 'phonix:fetch-images
                            {--dry-run : Preview without downloading}
                            {--force   : Re-download even when images already exist}
                            {--sku=    : Limit to one product by SKU or URL key}';

    protected $description = 'Assign real product-specific images from GSMArena / Wikimedia to Phonix products';

    /**
     * Map of URL-key prefix → ordered list of image URLs to try.
     * Longer/more specific prefixes must come FIRST so they match before shorter siblings.
     */
    protected function imageMap(): array
    {
        return [
            // ── Samsung Galaxy A-series ───────────────────────────────────────
            'samsung-galaxy-a06-5g'        => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a06-5g.jpg'],
            'samsung-galaxy-a07-4g'        => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a07.jpg'],
            'samsung-galaxy-a17'           => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a17.jpg'],
            'samsung-galaxy-a16'           => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a16-lte.jpg'],
            'samsung-galaxy-a26'           => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a26.jpg'],
            'samsung-galaxy-a36'           => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a36.jpg'],
            'samsung-galaxy-a56'           => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a56-.jpg'],

            // ── Samsung Galaxy Tab ────────────────────────────────────────────
            'samsung-galaxy-tab-s10-plus'  => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-tab-s10-plus.jpg'],
            'samsung-galaxy-tab-a11-10'    => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-tab-a9+.jpg'],
            'samsung-galaxy-tab-a11-8'     => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-tab-a7-lite.jpg'],
            'samsung-galaxy-tab-a11'       => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-tab-a7-lite.jpg'],

            // ── Samsung accessories ───────────────────────────────────────────
            'samsung-galaxy-buds-3-pro'    => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-buds3-pro.jpg'],
            'samsung-galaxy-buds-core'     => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-buds-core.jpg'],
            'samsung-galaxy-watch-7-ultra' => ['https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-watch-ultra.jpg'],

            // ── Tecno ─────────────────────────────────────────────────────────
            'tecno-spark-go-one'           => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-go1.jpg'],
            'tecno-spark-go-2'             => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-go-2.jpg'],
            'tecno-spark-go-3'             => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-go-3.jpg'],
            'tecno-spark-20-pro'           => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-20-pro.jpg'],
            'tecno-spark-slim'             => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-slim.jpg'],
            'tecno-spark-4c'               => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-spark-40c.jpg'],
            'tecno-pova-7-5g'              => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-pova7-5g.jpg'],
            'tecno-pova-7'                 => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-pova-7.jpg'],
            'tecno-camon-40-pro-5g'        => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-camon-40-pro-5g.jpg'],
            'tecno-camon-20-premier'       => ['https://fdn2.gsmarena.com/vv/bigpic/tecno-camon-20-premier.jpg'],

            // ── Apple iPhone ──────────────────────────────────────────────────
            'apple-iphone-17-pro-max'      => ['https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-17-pro-max.jpg'],
            'apple-iphone-17-pro'          => ['https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-17-pro.jpg'],
            'apple-iphone-17-air'          => ['https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-air.jpg'],
            'apple-iphone-17'              => ['https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-17.jpg'],
            'apple-iphone-16'              => ['https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-16.jpg'],
            'apple-iphone-15-pro'          => ['https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-15-pro.jpg'],

            // ── Apple accessories ─────────────────────────────────────────────
            'apple-airpods-pro-2-anc'      => ['https://fdn2.gsmarena.com/vv/bigpic/apple-airpods-pro-2nd-generation.jpg'],
            'apple-airpods-3rd-gen'        => ['https://fdn2.gsmarena.com/vv/bigpic/apple-airpods-3rd-generation.jpg'],
            'apple-airtag-4-pack'          => ['https://fdn2.gsmarena.com/vv/bigpic/apple-airtag.jpg'],
            'apple-pencil-2nd-gen'         => ['https://fdn2.gsmarena.com/vv/bigpic/apple-pencil-2nd-generation.jpg'],

            // ── Xiaomi / Redmi ────────────────────────────────────────────────
            'xiaomi-redmi-note-14-4g'      => ['https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-note-14-4g-gl.jpg'],
            'xiaomi-redmi-note-13-pro-5g'  => ['https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-note-13-pro-5g.jpg'],
            'xiaomi-redmi-15c'             => ['https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-15c-4g.jpg'],
            'xiaomi-redmi-a5'              => ['https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-a5-4g.jpg'],
            'xiaomi-redmi-a3'              => ['https://fdn2.gsmarena.com/vv/bigpic/xiaomi-redmi-a3.jpg'],

            // ── Tablets ───────────────────────────────────────────────────────
            'doogee-u10-10-wifi'           => ['https://fdn2.gsmarena.com/vv/bigpic/doogee-u10.jpg'],
            'teclast-p30t-10-wifi'         => ['https://fdn2.gsmarena.com/vv/bigpic/teclast-p30t.jpg'],

            // ── Gaming ────────────────────────────────────────────────────────
            'sony-playstation-5-1tb'       => ['https://fdn2.gsmarena.com/vv/bigpic/sony-playstation-5.jpg'],
        ];
    }

    /**
     * Category-based fallbacks (used when no specific map entry matches).
     */
    protected function categoryFallbacks(): array
    {
        return [
            'Samsung Phones'  => 'https://images.unsplash.com/photo-1567581935884-3349723552ca?w=900&h=900&fit=crop&q=90',
            'Tecno Phones'    => 'https://images.unsplash.com/photo-1546869846-e3c0bf6d1e4e?w=900&h=900&fit=crop&q=90',
            'Apple iPhones'   => 'https://images.unsplash.com/photo-1632661674596-df8be070a5c5?w=900&h=900&fit=crop&q=90',
            'Xiaomi Phones'   => 'https://images.unsplash.com/photo-1619406060869-96a6c7bd024e?w=900&h=900&fit=crop&q=90',
            'Tablets'         => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=900&h=900&fit=crop&q=90',
            'Accessories'     => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=900&h=900&fit=crop&q=90',
            'Gaming'          => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=900&h=900&fit=crop&q=90',
        ];
    }

    public function handle(): int
    {
        $dryRun  = $this->option('dry-run');
        $force   = $this->option('force');
        $skuOpt  = $this->option('sku');

        $this->info($dryRun ? '[DRY RUN] Scanning products...' : 'Fetching real product images...');

        // Load the URL map sorted by key length (longest first for best match)
        $map = $this->imageMap();
        uksort($map, fn($a, $b) => strlen($b) - strlen($a));

        // Get products — parent/standalone only (children inherit parent images)
        $query = DB::table('products')
            ->whereNull('parent_id')
            ->whereIn('type', ['simple', 'configurable']);

        if ($skuOpt) {
            $query->where('sku', 'LIKE', '%' . strtoupper($skuOpt) . '%');
        }

        $products = $query->get();

        $total   = $products->count();
        $updated = 0;
        $skipped = 0;
        $failed  = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($products as $product) {
            $urlKey = $this->getUrlKey($product->id);
            if (! $urlKey) {
                $bar->advance();
                $skipped++;
                continue;
            }

            // Skip if already has image and not forcing
            if (! $force && $this->hasImage($product->id)) {
                $bar->advance();
                $skipped++;
                continue;
            }

            // Find best URL from map
            $urls = $this->findUrls($urlKey, $map);

            // Add category fallback
            $categoryName = $this->getCategory($product->id);
            if ($categoryName && isset($this->categoryFallbacks()[$categoryName])) {
                $urls[] = $this->categoryFallbacks()[$categoryName];
            }

            if ($dryRun) {
                $bar->advance();
                $this->newLine();
                $this->line("  <comment>{$urlKey}</comment> → " . ($urls[0] ?? 'NO MATCH'));
                continue;
            }

            $downloaded = false;
            foreach ($urls as $url) {
                if ($this->downloadAndAssign($product->id, $url)) {
                    $downloaded = true;
                    $updated++;
                    break;
                }
            }

            if (! $downloaded) {
                $failed++;
                $this->newLine();
                $this->warn("  Failed: {$urlKey}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (! $dryRun) {
            $this->info("Done. Updated: {$updated} | Skipped: {$skipped} | Failed: {$failed}");
        }

        return self::SUCCESS;
    }

    protected function findUrls(string $urlKey, array $map): array
    {
        foreach ($map as $pattern => $urls) {
            if (str_starts_with($urlKey, $pattern)) {
                return $urls;
            }
        }
        return [];
    }

    protected function getUrlKey(int $productId): ?string
    {
        return DB::table('product_attribute_values')
            ->where('product_id', $productId)
            ->where('attribute_id', 3)
            ->whereNotNull('text_value')
            ->value('text_value');
    }

    protected function getCategory(int $productId): ?string
    {
        return DB::table('product_categories')
            ->join('category_translations', 'product_categories.category_id', '=', 'category_translations.category_id')
            ->where('product_categories.product_id', $productId)
            ->where('category_translations.locale', 'en')
            ->where('category_translations.category_id', '!=', 1)
            ->value('category_translations.name');
    }

    protected function hasImage(int $productId): bool
    {
        return DB::table('product_images')->where('product_id', $productId)->exists();
    }

    protected function downloadAndAssign(int $productId, string $url): bool
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'    => 20,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                    'header'     => [
                        'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
                        'Accept-Language: en-US,en;q=0.9',
                        'Referer: https://www.gsmarena.com/',
                    ],
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);

            $content = @file_get_contents($url, false, $context);

            if ($content === false || strlen($content) < 5000) {
                return false;
            }

            // Verify it's actually an image
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->buffer($content);
            if (! str_starts_with($mime, 'image/')) {
                return false;
            }

            $ext  = match ($mime) {
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'jpg',
            };

            // Remove old images
            $oldImages = DB::table('product_images')->where('product_id', $productId)->get();
            foreach ($oldImages as $img) {
                Storage::disk('public')->delete($img->path);
            }
            DB::table('product_images')->where('product_id', $productId)->delete();

            // Save new image
            $path = 'product/' . $productId . '/' . Str::random(20) . '.' . $ext;
            Storage::disk('public')->put($path, $content);

            DB::table('product_images')->insert([
                'type'       => 'images',
                'path'       => $path,
                'product_id' => $productId,
                'position'   => 1,
            ]);

            // Also propagate to children (configurable product variants)
            $children = DB::table('products')->where('parent_id', $productId)->pluck('id');
            foreach ($children as $childId) {
                $childOld = DB::table('product_images')->where('product_id', $childId)->get();
                foreach ($childOld as $img) {
                    Storage::disk('public')->delete($img->path);
                }
                DB::table('product_images')->where('product_id', $childId)->delete();

                $childPath = 'product/' . $childId . '/' . Str::random(20) . '.' . $ext;
                Storage::disk('public')->put($childPath, $content);
                DB::table('product_images')->insert([
                    'type'       => 'images',
                    'path'       => $childPath,
                    'product_id' => $childId,
                    'position'   => 1,
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
