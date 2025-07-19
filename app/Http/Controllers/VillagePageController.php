<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\Article;
use App\Models\Offer;
use App\Models\Place;
use App\Models\Image;
use App\Models\ExternalLink;
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
            ->with(['sme.community', 'category'])
            ->latest()
            ->take(6)
            ->get();

        $featuredPlaces = Place::where('village_id', $village->id)
            ->with('images')
            ->latest()
            ->take(4)
            ->get();

        $featuredImages = Image::where('village_id', $village->id)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return Inertia::render('Village/Home', [
            'village' => $village,
            'featuredArticles' => $featuredArticles,
            'featuredOffers' => $featuredOffers,
            'featuredPlaces' => $featuredPlaces,
            'featuredImages' => $featuredImages,
        ]);
    }

    public function articles(Request $request)
    {
        $village = $request->attributes->get('village');

        $articles = Article::where('village_id', $village->id)
            ->where('is_published', true)
            ->with(['community', 'sme', 'place'])
            ->latest('published_at')
            ->paginate(12);

        return Inertia::render('Village/Articles/Index', [
            'village' => $village,
            'articles' => $articles,
        ]);
    }

    public function articleShow(Request $request, string $slug)
    {
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

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by availability
        if ($request->filled('availability')) {
            $query->where('availability', $request->availability);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        $products = $query->with(['sme.community', 'category', 'tags'])
            ->latest()
            ->paginate(12);

        // Get available categories for filter
        $categories = \App\Models\Category::where('village_id', $village->id)
            ->withCount('offers')
            ->having('offers_count', '>', 0)
            ->get();

        return Inertia::render('Village/Products/Index', [
            'village' => $village,
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['category', 'availability', 'search']),
        ]);
    }

    public function productShow(Request $request, string $slug)
    {
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

        $places = Place::where('village_id', $village->id)
            ->with(['images' => function ($query) {
                $query->where('is_featured', true)->take(1);
            }])
            ->paginate(12);

        return Inertia::render('Village/Places/Index', [
            'village' => $village,
            'places' => $places,
        ]);
    }

    public function placeShow(Request $request, string $slug)
    {
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

        $images = Image::where('village_id', $village->id)
            ->with(['community', 'sme', 'place'])
            ->orderBy('sort_order')
            ->paginate(24);

        return Inertia::render('Village/Gallery', [
            'village' => $village,
            'images' => $images,
        ]);
    }
}
