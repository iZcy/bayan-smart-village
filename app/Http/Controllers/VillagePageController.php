<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\Article;
use App\Models\Offer;
use App\Models\Place;
use App\Models\Image;
use App\Models\ExternalLink;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VillagePageController extends Controller
{
    public function home(Request $request)
    {
        $village = $request->attributes->get('village');

        // Get featured content
        $featuredArticles = Article::where('village_id', $village->id)
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with(['community', 'sme', 'place'])
            ->latest('published_at')
            ->take(3)
            ->get();

        $featuredOffers = Offer::whereHas('sme.community', function ($query) use ($village) {
            $query->where('village_id', $village->id);
        })
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with(['sme.community', 'category', 'tags'])
            ->latest()
            ->take(6)
            ->get();

        $featuredPlaces = Place::where('village_id', $village->id)
            ->with(['images' => function ($query) {
                $query->where('is_featured', true)->take(1);
            }])
            ->latest()
            ->take(4)
            ->get();

        $featuredImages = Image::where('village_id', $village->id)
            ->where('is_featured', true)
            ->with(['place', 'community', 'sme'])
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return Inertia::render('Village/Home', [
            'village' => $village,
            'featuredArticles' => $featuredArticles,
            'featuredProducts' => $featuredOffers, // Note: renamed to match frontend expectation
            'featuredPlaces' => $featuredPlaces,
            'featuredImages' => $featuredImages,
        ]);
    }

    public function articles(Request $request)
    {
        $village = $request->attributes->get('village');

        $query = Article::where('village_id', $village->id)
            ->where('is_published', true)
            ->with(['community', 'sme', 'place']);

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $categoryId = $request->get('category');
            $query->where(function ($q) use ($categoryId) {
                $q->where('place_id', $categoryId)
                    ->orWhere('community_id', $categoryId)
                    ->orWhere('sme_id', $categoryId);
            });
        }

        $articles = $query->latest('published_at')->paginate(12);

        return Inertia::render('Village/Articles/Index', [
            'village' => $village,
            'articles' => $articles,
            'filters' => [
                'search' => $request->get('search', ''),
                'category' => $request->get('category', ''),
                'sort' => $request->get('sort', 'newest'),
            ],
        ]);
    }

    public function articleShow(Request $request)
    {
        $slug = last(explode('/', $request->getRequestUri()));

        $village = $request->attributes->get('village');

        $article = Article::where('village_id', $village->id)
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with(['community', 'sme', 'place'])
            ->firstOrFail();

        // Get related articles
        $relatedArticles = Article::where('village_id', $village->id)
            ->where('is_published', true)
            ->where('id', '!=', $article->id)
            ->with(['community', 'sme', 'place'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return Inertia::render('Village/Articles/Show', [
            'village' => $village,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    public function products(Request $request)
    {
        $village = $request->attributes->get('village');

        $query = Offer::whereHas('sme.community', function ($q) use ($village) {
            $q->where('village_id', $village->id);
        })->where('is_active', true);

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        if ($request->filled('availability')) {
            $query->where('availability', $request->get('availability'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'featured');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'price_low':
                $query->orderBy('price');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            default: // featured
                $query->orderBy('is_featured', 'desc')->latest();
        }

        $products = $query->with(['sme.community', 'category', 'tags'])
            ->paginate(12);

        // Get available categories for filter
        $categories = Category::where('village_id', $village->id)
            ->withCount(['offers' => function ($query) use ($village) {
                $query->whereHas('sme.community', function ($q) use ($village) {
                    $q->where('village_id', $village->id);
                })->where('is_active', true);
            }])
            ->having('offers_count', '>', 0)
            ->get();

        return Inertia::render('Village/Products/Index', [
            'village' => $village,
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $request->get('search', ''),
                'category' => $request->get('category', ''),
                'availability' => $request->get('availability', ''),
                'sort' => $request->get('sort', 'featured'),
            ],
        ]);
    }

    public function productShow(Request $request)
    {
        $slug = last(explode('/', $request->getRequestUri()));

        $village = $request->attributes->get('village');

        $product = Offer::whereHas('sme.community', function ($query) use ($village) {
            $query->where('village_id', $village->id);
        })
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'sme.community',
                'category',
                'tags',
                'images' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'ecommerceLinks' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order');
                }
            ])
            ->firstOrFail();

        // Increment view count
        $product->increment('view_count');

        // Get related products
        $relatedProducts = Offer::whereHas('sme.community', function ($query) use ($village) {
            $query->where('village_id', $village->id);
        })
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['sme.community', 'category'])
            ->take(4)
            ->get();

        return Inertia::render('Village/Products/Show', [
            'village' => $village,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function places(Request $request)
    {
        $village = $request->attributes->get('village');

        $query = Place::where('village_id', $village->id)
            ->with(['images' => function ($query) {
                $query->where('is_featured', true)->take(1);
            }]);

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('id', $request->get('category'));
            });
        }

        if ($request->filled('type')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('type', $request->get('type'));
            });
        }

        $places = $query->paginate(12);

        // Get available categories
        $categories = Category::where('village_id', $village->id)
            ->whereHas('places')
            ->withCount('places')
            ->get();

        return Inertia::render('Village/Places/Index', [
            'village' => $village,
            'places' => $places,
            'categories' => $categories,
            'filters' => [
                'search' => $request->get('search', ''),
                'category' => $request->get('category', ''),
                'type' => $request->get('type', ''),
            ],
        ]);
    }

    public function placeShow(Request $request)
    {
        $slug = last(explode('/', $request->getRequestUri()));

        $village = $request->attributes->get('village');

        $place = Place::where('village_id', $village->id)
            ->where('slug', $slug)
            ->with([
                'images' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'smes' => function ($query) {
                    $query->where('is_active', true)->with('community');
                },
                'articles' => function ($query) {
                    $query->where('is_published', true)->latest('published_at')->take(3);
                }
            ])
            ->firstOrFail();

        return Inertia::render('Village/Places/Show', [
            'village' => $village,
            'place' => $place,
        ]);
    }

    public function gallery(Request $request)
    {
        $village = $request->attributes->get('village');

        $query = Image::where('village_id', $village->id)
            ->with(['community', 'sme', 'place']);

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('caption', 'like', "%{$search}%")
                    ->orWhereHas('place', function ($placeQuery) use ($search) {
                        $placeQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('place')) {
            $query->where('place_id', $request->get('place'));
        }

        $images = $query->orderBy('sort_order')->paginate(24);

        // Get available places for filtering
        $places = Place::where('village_id', $village->id)
            ->whereHas('images')
            ->select('id', 'name')
            ->get();

        return Inertia::render('Village/Gallery', [
            'village' => $village,
            'images' => $images,
            'places' => $places,
            'filters' => [
                'search' => $request->get('search', ''),
                'place' => $request->get('place', ''),
            ],
        ]);
    }
}
