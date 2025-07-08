<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductEcommerceLink;
use App\Models\Village;
use App\Models\Category;
use App\Models\ProductTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with(['village', 'place', 'category', 'ecommerceLinks', 'tags'])
            ->active();

        // Apply filters
        if ($request->has('category')) {
            $query->byCategory($request->string('category'));
        }

        if ($request->has('village')) {
            $query->byVillage($request->string('village'));
        }

        if ($request->has('place')) {
            $query->byPlace($request->string('place'));
        }

        if ($request->has('search')) {
            $query->search($request->string('search'));
        }

        if ($request->has('min_price') || $request->has('max_price')) {
            $query->priceRange($request->integer('min_price'), $request->integer('max_price'));
        }

        if ($request->has('availability')) {
            $query->where('availability', $request->string('availability'));
        }

        if ($request->has('featured') && $request->boolean('featured')) {
            $query->featured();
        }

        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->string('tags'));
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        // Sorting
        $sortBy = $request->string('sort_by', 'created_at');
        $sortOrder = $request->string('sort_order', 'desc');

        $allowedSorts = ['created_at', 'name', 'price', 'view_count'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'price') {
                // Sort by lowest price (considering both fixed price and price ranges)
                $query->orderByRaw('COALESCE(price, price_range_min) ' . $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $products = $query->paginate($request->integer('per_page', 12));

        return response()->json([
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            'filters' => [
                'categories' => Category::all(['id', 'name', 'type']),
                'villages' => Village::active()->get(['id', 'name', 'slug']),
                'tags' => ProductTag::popular(20)->get(['id', 'name', 'slug']),
            ]
        ]);
    }

    /**
     * Display the specified product
     */
    public function show(Request $request, string $slug)
    {
        // Get village from middleware if on village subdomain
        $village = $request->attributes->get('village');

        $query = Product::with([
            'village',
            'place',
            'category',
            'ecommerceLinks' => function ($q) {
                $q->active()->ordered();
            },
            'images' => function ($q) {
                $q->ordered();
            },
            'tags'
        ])->active();

        if ($village) {
            $product = $query->where('village_id', $village->id)->where('slug', $slug)->first();
        } else {
            $product = $query->where('slug', $slug)->first();
        }

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Increment view count
        $product->incrementViewCount();

        // Get related products
        $relatedProducts = Product::with(['village', 'category', 'ecommerceLinks'])
            ->active()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('category_id', $product->category_id)
                    ->orWhere('village_id', $product->village_id);
            })
            ->limit(6)
            ->get();

        return response()->json([
            'product' => $product,
            'related_products' => $relatedProducts,
            'breadcrumbs' => $this->generateBreadcrumbs($product),
        ]);
    }

    /**
     * Get products for a specific village
     */
    public function villageProducts(Request $request)
    {
        $village = $request->attributes->get('village');

        if (!$village) {
            return response()->json(['error' => 'Village not found'], 404);
        }

        $query = Product::with(['place', 'category', 'ecommerceLinks', 'tags'])
            ->active()
            ->byVillage($village->id);

        // Apply filters
        if ($request->has('category')) {
            $query->byCategory($request->string('category'));
        }

        if ($request->has('search')) {
            $query->search($request->string('search'));
        }

        if ($request->has('featured') && $request->boolean('featured')) {
            $query->featured();
        }

        $products = $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 12));

        return response()->json([
            'village' => [
                'name' => $village->name,
                'slug' => $village->slug,
                'description' => $village->description,
            ],
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            'stats' => [
                'total_products' => $village->products()->active()->count(),
                'featured_products' => $village->products()->active()->featured()->count(),
                'categories' => $village->products()
                    ->active()
                    ->with('category')
                    ->get()
                    ->groupBy('category.name')
                    ->map->count(),
            ]
        ]);
    }

    /**
     * Get products for a specific place
     */
    public function placeProducts(Request $request, string $placeId)
    {
        $products = Product::with(['village', 'category', 'ecommerceLinks', 'tags'])
            ->active()
            ->byPlace($placeId)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 12));

        if ($products->isEmpty()) {
            return response()->json(['error' => 'No products found for this place'], 404);
        }

        $place = $products->first()->place;

        return response()->json([
            'place' => [
                'id' => $place->id,
                'name' => $place->name,
                'description' => $place->description,
                'village' => $place->village?->name,
            ],
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Get featured products
     */
    public function featured(Request $request)
    {
        $products = Product::with(['village', 'place', 'category', 'ecommerceLinks'])
            ->active()
            ->featured()
            ->orderBy('view_count', 'desc')
            ->limit($request->integer('limit', 8))
            ->get();

        return response()->json([
            'data' => $products,
            'total' => $products->count(),
        ]);
    }

    /**
     * Track e-commerce link clicks
     */
    public function trackLinkClick(Request $request, string $productId, string $linkId)
    {
        $product = Product::findOrFail($productId);
        $link = ProductEcommerceLink::where('product_id', $product->id)
            ->where('id', $linkId)
            ->active()
            ->firstOrFail();

        // Increment click count
        $link->incrementClickCount();

        // Log the click for analytics
        Log::info("E-commerce link clicked", [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'link_id' => $link->id,
            'platform' => $link->platform,
            'store_name' => $link->store_name,
            'target_url' => $link->final_url,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'referer' => $request->header('referer'),
            'village' => $product->village?->name,
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => $link->final_url,
            'platform' => $link->platform_name,
            'call_to_action' => $link->call_to_action,
        ]);
    }

    /**
     * Get product categories
     */
    public function categories()
    {
        $categories = Category::withCount(['products' => function ($query) {
            $query->active();
        }])
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get product tags
     */
    public function tags(Request $request)
    {
        $tags = ProductTag::when($request->has('search'), function ($query) use ($request) {
            $query->search($request->string('search'));
        })
            ->popular($request->integer('limit', 20))
            ->get();

        return response()->json([
            'data' => $tags,
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $query = $request->string('q');

        if (empty($query)) {
            return response()->json([
                'data' => [],
                'query' => $query,
                'total' => 0,
            ]);
        }

        $products = Product::with(['village', 'place', 'category', 'ecommerceLinks'])
            ->active()
            ->search($query)
            ->orWhereHas('tags', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('view_count', 'desc')
            ->limit($request->integer('limit', 10))
            ->get();

        // Also search for suggested tags
        $suggestedTags = ProductTag::search($query)
            ->limit(5)
            ->get();

        return response()->json([
            'data' => $products,
            'suggested_tags' => $suggestedTags,
            'query' => $query,
            'total' => $products->count(),
        ]);
    }

    /**
     * Get product analytics/stats
     */
    public function stats()
    {
        $stats = [
            'total_products' => Product::active()->count(),
            'featured_products' => Product::active()->featured()->count(),
            'total_views' => Product::active()->sum('view_count'),
            'products_with_ecommerce' => Product::active()->whereHas('ecommerceLinks')->count(),
            'top_categories' => Category::withCount(['products' => function ($query) {
                $query->active();
            }])
                ->having('products_count', '>', 0)
                ->orderBy('products_count', 'desc')
                ->limit(5)
                ->get(),
            'top_villages' => Village::withCount(['products' => function ($query) {
                $query->active();
            }])
                ->having('products_count', '>', 0)
                ->orderBy('products_count', 'desc')
                ->limit(5)
                ->get(),
            'platform_distribution' => ProductEcommerceLink::active()
                ->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->orderBy('count', 'desc')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Generate breadcrumbs for product
     */
    private function generateBreadcrumbs(Product $product): array
    {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Products', 'url' => '/products'],
        ];

        if ($product->village) {
            $breadcrumbs[] = [
                'name' => $product->village->name,
                'url' => "/villages/{$product->village->slug}/products"
            ];
        }

        if ($product->category) {
            $breadcrumbs[] = [
                'name' => $product->category->name,
                'url' => "/products?category={$product->category->id}"
            ];
        }

        $breadcrumbs[] = [
            'name' => $product->name,
            'url' => $product->url,
            'current' => true
        ];

        return $breadcrumbs;
    }
}
