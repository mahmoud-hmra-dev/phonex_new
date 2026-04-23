<?php

declare(strict_types=1);

namespace Webkul\Phonix\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Models\Product;

/**
 * Filter-aware product listing.
 *
 * Every query goes through this one method. Each filter is applied
 * independently to the products query builder. Sorts and pagination
 * work on top of whatever subset remains.
 *
 * We query the products table directly and join the two indexes that
 * Bagisto's indexer maintains:
 *   - product_price_indices     (for price range + price sort)
 *   - product_inventory_indices (for in-stock filter)
 *
 * If either index is empty (fresh install / seeded without indexing),
 * we rebuild it on the fly so filters never show an empty store.
 */
class ProductListingController
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Render the product listing for the shop.
     *
     * @param  \Webkul\Category\Contracts\Category|null  $category  // optional, for category URL
     */
    public function index(Request $request, $category = null)
    {
        $this->ensureIndices();

        $channel         = core()->getCurrentChannel();
        $customerGroupId = $this->currentCustomerGroupId();
        $locale          = app()->getLocale();

        // ── Filter inputs ────────────────────────────────────────────────
        $query       = trim((string) $request->input('query', ''));
        $categoryIds = $this->arrayOfInts($request->input('category_ids', []));
        $brandIds    = $this->arrayOfInts($request->input('brand_ids', []));
        $rating      = (int) $request->input('rating', 0);
        $inStock     = $request->filled('in_stock');
        $onSale      = $request->filled('on_sale');
        $sortParam   = (string) $request->input('sort', 'created_at-desc');
        $perPage     = max(1, min(96, (int) $request->input('limit', 12)));
        $priceParam  = trim((string) $request->input('price', ''));

        // Legacy single category_id param (compat with older links)
        if (! $categoryIds && $request->filled('category_id')) {
            $categoryIds = [(int) $request->input('category_id')];
        }

        // When exactly one category is selected, resolve it so the page can
        // show its name in the breadcrumb / heading. We keep the filter UI
        // enabled either way — users can add or remove categories freely.
        if ($category === null && count($categoryIds) === 1) {
            $category = $this->categoryRepository->find($categoryIds[0]);
        }

        // Price min/max
        [$priceMin, $priceMax] = $this->parsePriceRange($priceParam);

        // ── Base query ───────────────────────────────────────────────────
        $q = Product::query()
            ->select('products.*')
            ->distinct()
            ->whereIn('products.type', ['simple', 'configurable'])
            ->whereNull('products.parent_id');

        // Status = 1 (enabled) — EAV
        $this->whereAttribute($q, 'status', fn ($sq) => $sq->where('pav.boolean_value', 1));

        // Visible individually = 1
        $this->whereAttribute($q, 'visible_individually', fn ($sq) => $sq->where('pav.boolean_value', 1));

        // Search by name (EAV text_value) — locale may be populated, channel often null
        if ($query !== '') {
            $this->whereAttribute($q, 'name', function ($sq) use ($query, $locale) {
                $sq->where('pav.text_value', 'like', "%{$query}%")
                    ->where(function ($w) use ($locale) {
                        $w->where('pav.locale', $locale)->orWhereNull('pav.locale');
                    });
            });
        }

        // Category filter
        if ($categoryIds) {
            $q->whereIn('products.id', function ($sq) use ($categoryIds) {
                $sq->select('product_id')
                    ->from('product_categories')
                    ->whereIn('category_id', $categoryIds);
            });
        }

        // Brand filter (attribute option ids)
        if ($brandIds) {
            $this->whereAttribute($q, 'brand', fn ($sq) => $sq->whereIn('pav.integer_value', $brandIds));
        }

        // Price index join for range + price sort
        $joinedPriceIndex = false;
        if ($priceMin !== null || $priceMax !== null || str_starts_with($sortParam, 'price-')) {
            $q->leftJoin('product_price_indices as ppi', function ($j) use ($channel, $customerGroupId) {
                $j->on('ppi.product_id', '=', 'products.id')
                    ->where('ppi.channel_id', '=', $channel->id)
                    ->where('ppi.customer_group_id', '=', $customerGroupId);
            });
            $joinedPriceIndex = true;
        }

        if ($priceMin !== null) {
            $q->where('ppi.min_price', '>=', $priceMin);
        }
        if ($priceMax !== null) {
            $q->where('ppi.min_price', '<=', $priceMax);
        }

        // On sale = regular_min_price > min_price
        if ($onSale) {
            if (! $joinedPriceIndex) {
                $q->leftJoin('product_price_indices as ppi', function ($j) use ($channel, $customerGroupId) {
                    $j->on('ppi.product_id', '=', 'products.id')
                        ->where('ppi.channel_id', '=', $channel->id)
                        ->where('ppi.customer_group_id', '=', $customerGroupId);
                });
                $joinedPriceIndex = true;
            }
            $q->whereColumn('ppi.regular_min_price', '>', 'ppi.min_price');
        }

        // In-stock filter
        if ($inStock) {
            $q->join('product_inventory_indices as pii', function ($j) use ($channel) {
                $j->on('pii.product_id', '=', 'products.id')
                    ->where('pii.channel_id', '=', $channel->id);
            })->where('pii.qty', '>', 0);
        }

        // Rating filter — join aggregate rating from product_reviews
        if ($rating > 0 && $rating <= 5) {
            $q->whereIn('products.id', function ($sq) use ($rating) {
                $sq->select('product_id')
                    ->from('product_reviews')
                    ->where('status', 'approved')
                    ->groupBy('product_id')
                    ->havingRaw('AVG(rating) >= ?', [$rating]);
            });
        }

        // ── Sort ─────────────────────────────────────────────────────────
        [$sortBy, $sortDir] = array_pad(explode('-', $sortParam, 2), 2, 'desc');
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc'], true) ? strtolower($sortDir) : 'desc';

        switch ($sortBy) {
            case 'price':
                // min_price from joined price index
                $q->orderBy('ppi.min_price', $sortDir);
                break;

            case 'name':
                $this->joinAttribute($q, 'name', 'pav_name', $locale, $channel->code);
                $q->orderBy('pav_name.text_value', $sortDir);
                break;

            case 'created_at':
            default:
                $q->orderBy('products.created_at', $sortDir);
                break;
        }

        // ── Paginate ────────────────────────────────────────────────────
        $products = $q->paginate($perPage)->withQueryString();

        // ── Context for view ─────────────────────────────────────────────
        $categoryTree = $this->categoryRepository->getVisibleCategoryTree($channel->root_category_id)
            ->filter(fn ($c) => $c->id !== 1)
            ->values();

        $brands = $this->brandOptions();

        // Pre-hydrate wishlist ids for logged-in customers
        $wishlistIds = [];
        if (auth('customer')->check()) {
            $wishlistIds = \Webkul\Customer\Models\Wishlist::where('customer_id', auth('customer')->id())
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        // Absolute price bounds for slider
        $priceBounds = DB::table('product_price_indices')
            ->where('channel_id', $channel->id)
            ->where('customer_group_id', $customerGroupId)
            ->selectRaw('MIN(min_price) as mn, MAX(min_price) as mx')
            ->first();
        $absoluteMin = (int) floor((float) ($priceBounds->mn ?? 0));
        $absoluteMax = (int) ceil((float) ($priceBounds->mx ?? 10000));
        if ($absoluteMax <= $absoluteMin) {
            $absoluteMax = $absoluteMin + 100;
        }

        return view('phonix::products.index', [
            'products'         => $products,
            'categoryTree'     => $categoryTree,
            'brands'           => $brands,
            'wishlistIds'      => $wishlistIds,
            'currentCategory'  => $category,

            // filter state
            'filters' => [
                'query'        => $query,
                'category_ids' => $categoryIds,
                'brand_ids'    => $brandIds,
                'rating'       => $rating,
                'in_stock'     => $inStock,
                'on_sale'      => $onSale,
                'sort'         => $sortParam,
                'limit'        => $perPage,
                'price_min'    => $priceMin,
                'price_max'    => $priceMax,
            ],
            'priceAbsoluteMin' => $absoluteMin,
            'priceAbsoluteMax' => $absoluteMax,
        ]);
    }

    /**
     * Ensure the indices Bagisto relies on are populated.
     * Rebuilds only if empty — zero cost on a healthy store.
     */
    protected function ensureIndices(): void
    {
        $channel = core()->getCurrentChannel();

        // Price index
        $priceRows = DB::table('product_price_indices')->where('channel_id', $channel->id)->count();
        $productRows = DB::table('products')->whereIn('type', ['simple', 'configurable', 'virtual'])->count();

        if ($priceRows === 0 && $productRows > 0) {
            $this->rebuildPriceIndex($channel);
        }

        // Inventory index
        $invRows = DB::table('product_inventory_indices')->where('channel_id', $channel->id)->count();
        if ($invRows < 2 && $productRows > 0) {
            $this->rebuildInventoryIndex($channel);
        }
    }

    protected function rebuildPriceIndex($channel): void
    {
        DB::table('product_price_indices')->where('channel_id', $channel->id)->delete();
        $groupId = $this->currentCustomerGroupId();
        $rows = [];

        foreach (Product::whereIn('type', ['simple', 'virtual'])->get() as $p) {
            $price = (float) ($p->price ?? 0);
            if ($price <= 0) {
                continue;
            }
            $special = (float) ($p->getTypeInstance()->haveDiscount()
                ? $p->getTypeInstance()->getMinimalPrice()
                : $price);
            $min = min($price, $special);
            $rows[] = [
                'product_id'        => $p->id,
                'channel_id'        => $channel->id,
                'customer_group_id' => $groupId,
                'min_price'         => $min,
                'regular_min_price' => $price,
                'max_price'         => $price,
                'regular_max_price' => $price,
            ];
        }

        foreach (Product::where('type', 'configurable')->get() as $cp) {
            $prices = Product::where('parent_id', $cp->id)->pluck('price')->filter()->map(fn ($v) => (float) $v);
            if ($prices->isEmpty()) {
                continue;
            }
            $rows[] = [
                'product_id'        => $cp->id,
                'channel_id'        => $channel->id,
                'customer_group_id' => $groupId,
                'min_price'         => $prices->min(),
                'regular_min_price' => $prices->min(),
                'max_price'         => $prices->max(),
                'regular_max_price' => $prices->max(),
            ];
        }

        if ($rows) {
            DB::table('product_price_indices')->insert($rows);
        }
    }

    protected function rebuildInventoryIndex($channel): void
    {
        DB::table('product_inventory_indices')->where('channel_id', $channel->id)->delete();
        $sourceIds = $channel->inventory_sources->where('status', 1)->pluck('id')->all();
        if (! $sourceIds) {
            return;
        }
        $rows = [];
        foreach (Product::whereIn('type', ['simple', 'virtual'])->with('inventories')->get() as $p) {
            $qty = 0;
            foreach ($p->inventories as $inv) {
                if (in_array($inv->inventory_source_id, $sourceIds, true)) {
                    $qty += (int) $inv->qty;
                }
            }
            $rows[] = [
                'product_id' => $p->id,
                'channel_id' => $channel->id,
                'qty'        => $qty,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($rows) {
            DB::table('product_inventory_indices')->insert($rows);
        }
    }

    /**
     * Add EAV attribute predicate as a WHERE EXISTS.
     */
    protected function whereAttribute($query, string $code, \Closure $predicate): void
    {
        $query->whereExists(function ($sq) use ($code, $predicate) {
            $sq->selectRaw('1')
                ->from('product_attribute_values as pav')
                ->join('attributes as at', 'pav.attribute_id', '=', 'at.id')
                ->whereColumn('pav.product_id', 'products.id')
                ->where('at.code', $code);
            $predicate($sq);
        });
    }

    /**
     * Join an EAV attribute for sorting/select purposes.
     */
    protected function joinAttribute($query, string $code, string $alias, string $locale, string $channelCode): void
    {
        $attribute = DB::table('attributes')->where('code', $code)->first();
        if (! $attribute) {
            return;
        }

        $query->leftJoin("product_attribute_values as {$alias}", function ($j) use ($alias, $attribute, $locale, $channelCode) {
            $j->on("{$alias}.product_id", '=', 'products.id')
                ->where("{$alias}.attribute_id", '=', $attribute->id);
            $j->where(function ($w) use ($alias, $locale) {
                $w->where("{$alias}.locale", $locale)->orWhereNull("{$alias}.locale");
            });
            $j->where(function ($w) use ($alias, $channelCode) {
                $w->where("{$alias}.channel", $channelCode)->orWhereNull("{$alias}.channel");
            });
        });
    }

    protected function brandOptions(): array
    {
        $brandAttr = DB::table('attributes')->where('code', 'brand')->first();
        if (! $brandAttr) {
            return [];
        }

        $rows = DB::table('attribute_options as o')
            ->leftJoin('attribute_option_translations as t', function ($j) {
                $j->on('t.attribute_option_id', '=', 'o.id')
                    ->where('t.locale', app()->getLocale());
            })
            ->where('o.attribute_id', $brandAttr->id)
            ->orderBy('o.sort_order')
            ->selectRaw('o.id as id, COALESCE(t.label, o.admin_name) as label')
            ->get();

        return $rows->map(fn ($r) => ['id' => (int) $r->id, 'label' => $r->label])->all();
    }

    protected function parsePriceRange(string $param): array
    {
        if (! Str::contains($param, ',')) {
            return [null, null];
        }
        [$min, $max] = array_pad(explode(',', $param, 2), 2, '');
        $min = $min === '' ? null : (float) $min;
        $max = $max === '' ? null : (float) $max;
        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }
        return [$min, $max];
    }

    protected function arrayOfInts($val): array
    {
        if (! is_array($val)) {
            $val = array_filter(explode(',', (string) $val));
        }
        return array_values(array_unique(array_filter(array_map('intval', $val))));
    }

    protected function currentCustomerGroupId(): int
    {
        if (auth('customer')->check()) {
            $customer = auth('customer')->user();
            if ($customer->customer_group_id) {
                return (int) $customer->customer_group_id;
            }
        }
        $guest = DB::table('customer_groups')->where('code', 'guest')->value('id');
        return (int) ($guest ?: 1);
    }
}
