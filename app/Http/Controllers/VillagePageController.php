<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Image;
use App\Models\Product;
use App\Models\SmeTourismPlace;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VillagePageController extends Controller
{
    public function home(Request $request)
    {
        $village = $request->attributes->get('village');

        if (!$village) {
            abort(404, 'Village not found');
        }

        // Get tourism places (limit to 8 for performance)
        $tourismPlaces = $village->places()
            ->whereHas('category', function ($query) {
                $query->where('type', 'tourism');
            })
            ->with(['category', 'images'])
            ->limit(8)
            ->get();

        // Get SME places (limit to 8 for performance)
        $smePlaces = $village->places()
            ->whereHas('category', function ($query) {
                $query->where('type', 'sme');
            })
            ->with(['category', 'images'])
            ->limit(8)
            ->get();

        // Get recent articles (limit to 6)
        $articles = $village->articles()
            ->latest()
            ->limit(6)
            ->get();

        // Get gallery images (limit to 12)
        $gallery = $village->images()
            ->with('place')
            ->latest()
            ->limit(12)
            ->get();

        // Get featured products
        $products = $village->products()
            ->active()
            ->featured()
            ->with(['category', 'ecommerceLinks'])
            ->limit(6)
            ->get();

        return Inertia::render('VillageHomePage', [
            'village' => $village,
            'places' => [
                'tourism' => $tourismPlaces,
                'sme' => $smePlaces,
            ],
            'articles' => $articles,
            'gallery' => $gallery,
            'products' => $products,
        ]);
    }

    public function articles(Request $request)
    {
        $village = $request->attributes->get('village');

        if (!$village) {
            abort(404, 'Village not found');
        }

        $articles = $village->articles()
            ->with(['place'])
            ->when($request->search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12);

        return Inertia::render('Village/ArticlesPage', [
            'village' => $village,
            'articles' => $articles,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function articleShow(Request $request, Article $article)
    {
        $village = $request->attributes->get('village');

        if (!$village || $article->village_id !== $village->id) {
            abort(404, 'Article not found');
        }

        $article->load(['place', 'village']);

        // Get related articles
        $relatedArticles = $village->articles()
            ->where('id', '!=', $article->id)
            ->latest()
            ->limit(3)
            ->get();

        return Inertia::render('Village/ArticleShowPage', [
            'village' => $village,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    public function products(Request $request)
    {
        $village = $request->attributes->get('village');

        if (!$village) {
            abort(404, 'Village not found');
        }

        $products = $village->products()
            ->active()
            ->with(['category', 'place', 'ecommerceLinks', 'tags'])
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                return $query->where('category_id', $category);
            })
            ->when($request->sort, function ($query, $sort) {
                switch ($sort) {
                    case 'name':
                        return $query->orderBy('name');
                    case 'price_low':
                        return $query->orderByRaw('COALESCE(price, price_range_min) ASC');
                    case 'price_high':
                        return $query->orderByRaw('COALESCE(price, price_range_min) DESC');
                    case 'popular':
                        return $query->orderBy('view_count', 'desc');
                    default:
                        return $query->latest();
                }
            }, function ($query) {
                return $query->orderBy('is_featured', 'desc')->latest();
            })
            ->paginate(12);

        // Get categories for filters
        $categories = $village->products()
            ->active()
            ->with('category')
            ->get()
            ->pluck('category')
            ->unique('id')
            ->values();

        return Inertia::render('Village/ProductsPage', [
            'village' => $village,
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
                'sort' => $request->sort,
            ],
        ]);
    }

    public function productShow(Request $request, Product $product)
    {
        $village = $request->attributes->get('village');

        if (!$village || $product->village_id !== $village->id) {
            abort(404, 'Product not found');
        }

        $product->load([
            'village',
            'place',
            'category',
            'ecommerceLinks' => function ($query) {
                $query->active()->ordered();
            },
            'images' => function ($query) {
                $query->ordered();
            },
            'tags'
        ]);

        // Increment view count
        $product->incrementViewCount();

        // Get related products
        $relatedProducts = $village->products()
            ->active()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('category_id', $product->category_id)
                    ->orWhere('place_id', $product->place_id);
            })
            ->with(['category', 'ecommerceLinks'])
            ->limit(4)
            ->get();

        return Inertia::render('Village/ProductShowPage', [
            'village' => $village,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function places(Request $request)
    {
        $village = $request->attributes->get('village');

        if (!$village) {
            abort(404, 'Village not found');
        }

        $places = $village->places()
            ->with(['category', 'images'])
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                return $query->whereHas('category', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            })
            ->when($request->category, function ($query, $category) {
                return $query->where('category_id', $category);
            })
            ->latest()
            ->paginate(12);

        // Get categories for filters
        $categories = $village->places()
            ->with('category')
            ->get()
            ->pluck('category')
            ->unique('id')
            ->values();

        return Inertia::render('Village/PlacesPage', [
            'village' => $village,
            'places' => $places,
            'categories' => $categories,
            'filters' => [
                'search' => $request->search,
                'type' => $request->type,
                'category' => $request->category,
            ],
        ]);
    }

    public function placeShow(Request $request, SmeTourismPlace $place)
    {
        $village = $request->attributes->get('village');

        if (!$village || $place->village_id !== $village->id) {
            abort(404, 'Place not found');
        }

        $place->load([
            'village',
            'category',
            'images',
            'articles',
            'externalLinks' => function ($query) {
                $query->active()->ordered();
            },
            'products' => function ($query) {
                $query->active()->limit(6);
            }
        ]);

        // Get related places
        $relatedPlaces = $village->places()
            ->where('id', '!=', $place->id)
            ->where('category_id', $place->category_id)
            ->with(['category', 'images'])
            ->limit(4)
            ->get();

        return Inertia::render('Village/PlaceShowPage', [
            'village' => $village,
            'place' => $place,
            'relatedPlaces' => $relatedPlaces,
        ]);
    }

    public function gallery(Request $request)
    {
        $village = $request->attributes->get('village');

        if (!$village) {
            abort(404, 'Village not found');
        }

        $images = $village->images()
            ->with(['place'])
            ->when($request->search, function ($query, $search) {
                return $query->where('caption', 'like', "%{$search}%")
                    ->orWhereHas('place', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->place, function ($query, $place) {
                return $query->where('place_id', $place);
            })
            ->latest()
            ->paginate(24);

        // Get places for filters
        $places = $village->places()
            ->whereHas('images')
            ->select('id', 'name')
            ->get();

        return Inertia::render('Village/GalleryPage', [
            'village' => $village,
            'images' => $images,
            'places' => $places,
            'filters' => [
                'search' => $request->search,
                'place' => $request->place,
            ],
        ]);
    }
}
