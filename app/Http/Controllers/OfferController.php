<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Offer;
use App\Models\OfferEcommerceLink;
use App\Models\OfferTag;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OfferController extends Controller
{
    /**
     * Display a listing of products (offers)
     */
    public function index(Request $request)
    {
        $query = Offer::with(['sme.community.village', 'category', 'tags', 'primaryImage'])
            ->where('is_active', true);

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by village
        if ($request->filled('village')) {
            $query->whereHas('sme.community.village', function ($q) use ($request) {
                $q->where('slug', $request->village);
            });
        }

        // Filter by tags
        if ($request->filled('tags')) {
            $tags = is_array($request->tags) ? $request->tags : [$request->tags];
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        // Filter by availability
        if ($request->filled('availability')) {
            $query->where('availability', $request->availability);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {
                        $tagQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(config('smartvillage.products.per_page', 12));

        return response()->json([
            'products' => $products,
            'filters' => $request->only(['category', 'village', 'tags', 'availability', 'min_price', 'max_price', 'search', 'sort']),
        ]);
    }

    /**
     * Display featured products
     */
    public function featured(Request $request)
    {
        $limit = $request->get('limit', config('smartvillage.products.featured_limit', 8));

        $products = Offer::with(['sme.community.village', 'category', 'primaryImage'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take($limit)
            ->get();

        return response()->json([
            'featured_products' => $products,
        ]);
    }

    /**
     * Display a single product
     */
    public function show(Request $request, string $slug)
    {
        $product = Offer::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'sme.community.village',
                'category',
                'tags',
                'additionalImages',
                'ecommerceLinks' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order');
                },
            ])
            ->firstOrFail();

        // Increment view count
        $product->increment('view_count');

        // Get related products from same category
        $relatedProducts = Offer::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['sme.community.village', 'category', 'primaryImage'])
            ->take(4)
            ->get();

        return response()->json([
            'product' => $product,
            'related_products' => $relatedProducts,
        ]);
    }

    /**
     * Get products for a specific village
     */
    public function villageProducts(Request $request)
    {
        $village = $request->attributes->get('village');

        if (! $village) {
            return response()->json(['error' => 'Village not found'], 404);
        }

        $query = Offer::whereHas('sme.community', function ($q) use ($village) {
            $q->where('village_id', $village->id);
        })
            ->where('is_active', true)
            ->with(['sme.community', 'category', 'tags', 'primaryImage']);

        // Apply same filters as index method
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(12);

        return response()->json([
            'village' => $village->name,
            'products' => $products,
        ]);
    }

    /**
     * Get available categories
     */
    public function categories(Request $request)
    {
        $categories = Category::withCount(['offers' => function ($query) {
            $query->where('is_active', true);
        }])
            ->having('offers_count', '>', 0)
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Get available tags
     */
    public function tags(Request $request)
    {
        $tags = OfferTag::withCount(['offers' => function ($query) {
            $query->where('is_active', true);
        }])
            ->having('offers_count', '>', 0)
            ->orderBy('usage_count', 'desc')
            ->get();

        return response()->json([
            'tags' => $tags,
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $search = $request->get('q');

        $products = Offer::where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {
                        $tagQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('name', 'like', "%{$search}%");
                    });
            })
            ->with(['sme.community.village', 'category', 'tags', 'primaryImage'])
            ->latest()
            ->paginate(12);

        return response()->json([
            'search_query' => $search,
            'products' => $products,
        ]);
    }

    /**
     * Track e-commerce link clicks
     */
    public function trackLinkClick(Request $request, Offer $product, OfferEcommerceLink $link)
    {
        // Verify the link belongs to the product
        if ($link->offer_id !== $product->id) {
            abort(404);
        }

        // Increment click count
        $link->increment('click_count');

        return response()->json([
            'message' => 'Click tracked successfully',
            'redirect_url' => $link->product_url,
        ]);
    }

    /**
     * Get product statistics
     */
    public function stats(Request $request)
    {
        $stats = Cache::remember('product_stats', 300, function () {
            return [
                'total_products' => Offer::where('is_active', true)->count(),
                'featured_products' => Offer::where('is_active', true)->where('is_featured', true)->count(),
                'total_categories' => Category::withCount(['offers' => function ($query) {
                    $query->where('is_active', true);
                }])->having('offers_count', '>', 0)->count(),
                'total_villages' => Village::whereHas('communities.smes.offers', function ($query) {
                    $query->where('is_active', true);
                })->count(),
                'availability_stats' => Offer::where('is_active', true)
                    ->selectRaw('availability, COUNT(*) as count')
                    ->groupBy('availability')
                    ->pluck('count', 'availability'),
                'top_categories' => Category::withCount(['offers' => function ($query) {
                    $query->where('is_active', true);
                }])
                    ->having('offers_count', '>', 0)
                    ->orderBy('offers_count', 'desc')
                    ->take(5)
                    ->get(),
            ];
        });

        return response()->json([
            'statistics' => $stats,
        ]);
    }
}
