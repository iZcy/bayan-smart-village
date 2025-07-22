<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\Offer;
use App\Models\Place;
use App\Models\Article;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Health check endpoint
     */
    public function health()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
        ]);
    }

    /**
     * Get all villages
     */
    public function villages()
    {
        $villages = Village::active()
            ->select(['id', 'name', 'slug', 'description', 'latitude', 'longitude'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $villages,
            'count' => $villages->count(),
        ]);
    }

    /**
     * Get single village by slug
     */
    public function village($slug)
    {
        $village = Village::where('slug', $slug)
            ->active()
            ->first();

        if (!$village) {
            return response()->json([
                'message' => 'Village not found',
            ], 404);
        }

        return response()->json([
            'data' => $village,
        ]);
    }

    /**
     * Global search across all content
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $limit = min($request->get('limit', 20), 100);

        if (!$query) {
            return response()->json([
                'message' => 'Search query is required',
            ], 400);
        }

        $results = [];

        // Search places
        $places = Place::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with('village:id,name,slug')
            ->limit($limit / 3)
            ->get();

        $results['places'] = $places->map(function ($place) {
            return [
                'type' => 'place',
                'id' => $place->id,
                'name' => $place->name,
                'description' => $place->description,
                'village' => $place->village,
                'url' => route('village.places.show', [$place->village->slug, $place->slug]),
            ];
        });

        // Search offers/products
        $offers = Offer::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with(['sme.village:id,name,slug'])
            ->limit($limit / 3)
            ->get();

        $results['products'] = $offers->map(function ($offer) {
            return [
                'type' => 'product',
                'id' => $offer->id,
                'name' => $offer->name,
                'description' => $offer->description,
                'price' => $offer->price,
                'village' => $offer->sme->village,
                'url' => route('village.products.show', [$offer->sme->village->slug, $offer->slug]),
            ];
        });

        // Search articles
        $articles = Article::where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->with('village:id,name,slug')
            ->limit($limit / 3)
            ->get();

        $results['articles'] = $articles->map(function ($article) {
            return [
                'type' => 'article',
                'id' => $article->id,
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'village' => $article->village,
                'url' => route('village.articles.show', [$article->village->slug, $article->slug]),
            ];
        });

        return response()->json([
            'query' => $query,
            'results' => $results,
            'total' => $results['places']->count() + $results['products']->count() + $results['articles']->count(),
        ]);
    }

    /**
     * Get popular content
     */
    public function popular()
    {
        // This would typically use view counts, but for now just return featured content
        $popularPlaces = Place::featured()
            ->with('village:id,name,slug')
            ->limit(5)
            ->get();

        $popularProducts = Offer::featured()
            ->with(['sme.village:id,name,slug'])
            ->limit(5)
            ->get();

        $popularArticles = Article::featured()
            ->with('village:id,name,slug')
            ->limit(5)
            ->get();

        return response()->json([
            'places' => $popularPlaces,
            'products' => $popularProducts,
            'articles' => $popularArticles,
        ]);
    }

    /**
     * Get system statistics
     */
    public function stats()
    {
        $stats = [
            'villages' => Village::active()->count(),
            'places' => Place::count(),
            'products' => Offer::count(),
            'articles' => Article::count(),
        ];

        return response()->json($stats);
    }
}