<?php

namespace Webkul\Phonix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssignProductImage extends Command
{
    protected $signature = 'phonix:assign-image {product-id : Product ID} {url : Direct image URL}';

    protected $description = 'Download and assign a specific image URL to a product (and its children)';

    public function handle(): int
    {
        $productId = (int) $this->argument('product-id');
        $url       = $this->argument('url');

        if (! DB::table('products')->where('id', $productId)->exists()) {
            $this->error("Product {$productId} not found.");
            return self::FAILURE;
        }

        $name = DB::table('product_attribute_values')
            ->where('product_id', $productId)
            ->where('attribute_id', 2)
            ->value('text_value') ?? "Product #{$productId}";

        $this->info("Downloading image for: {$name}");
        $this->line("URL: {$url}");

        $context = stream_context_create([
            'http' => [
                'timeout'    => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0 Safari/537.36',
                'header'     => [
                    'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer: https://www.gsmarena.com/',
                ],
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $content = @file_get_contents($url, false, $context);

        if ($content === false || strlen($content) < 5000) {
            $this->error('Failed to download image (empty or too small response).');
            return self::FAILURE;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->buffer($content);

        if (! str_starts_with($mime, 'image/')) {
            $this->error("Response is not an image (got: {$mime}). URL may be blocking hotlinks.");
            return self::FAILURE;
        }

        $ext = match ($mime) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        // Collect parent + children
        $ids = collect([$productId]);
        $ids = $ids->merge(DB::table('products')->where('parent_id', $productId)->pluck('id'));

        foreach ($ids as $id) {
            $old = DB::table('product_images')->where('product_id', $id)->get();
            foreach ($old as $img) {
                Storage::disk('public')->delete($img->path);
            }
            DB::table('product_images')->where('product_id', $id)->delete();

            $path = 'product/' . $id . '/' . Str::random(20) . '.' . $ext;
            Storage::disk('public')->put($path, $content);

            DB::table('product_images')->insert([
                'type'       => 'images',
                'path'       => $path,
                'product_id' => $id,
                'position'   => 1,
            ]);
        }

        $this->info("Image assigned to product {$productId} and " . ($ids->count() - 1) . " children. Size: " . number_format(strlen($content) / 1024, 1) . " KB");

        return self::SUCCESS;
    }
}
