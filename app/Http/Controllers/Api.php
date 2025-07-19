<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use App\Models\Offer;
use App\Models\Article;
use App\Models\Place;
use App\Models\Category;
use App\Models\OfferTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    /**
     * Get all villages
     */
    public function villages(Request $request)
    {
        $villages = Village::where('is_active', true)
            ->withCount([
                'communities' => function ($query) {
                    $query->where('is_active', true);
                },
                'places',
                'articles' => function ($query) {
                    $query->where('is_published', true);
                }
            ])
            ->get()
            ->map(function ($village) {
                return [
                    'id' => $village->id,
                    'name' => $village->name,
                    'slug' => $village->slug,
                    'description' => $village->description,
                    'domain' => $village->domain,
                    'image_url' => $village->image_url,
                    'communities_count' => $village->communities_count,
                    'places_count' => $village->places_count,
                    'articles_count' => $village->articles_count,
                    'established_at' => $village->established_at,
                ];
            });

        return response()->json([
            'villages' => $villages
        ]);
    }

    /**
     * Get village by slug
     */
    public function village(Request $request, string $slug)
    {
        $village = Village::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'communities' => function ($query) {
                    $query->where('is_active', true)->withCount('smes');
                },
                'places',
                'categories'
            ])
            ->firstOrFail();

        return response()->json([
            'village' => [
                'id' => $village->id,
                'name' => $village->name,
                'slug' => $village->slug,
                'description' => $village->description,
                'domain' => $village->domain,
                'phone_number' => $village->phone_number,
                'email' => $village->email,
                'address' => $village->address,
                'image_url' => $village->image_url,
                'latitude' => $village->latitude,
                'longitude' => $village->longitude,
                'established_at' => $village->established_at,
                'communities' => $village->communities,
                'places' => $village->places,
                'categories' => $village->categories,
            ]
        ]);
    }

    /**
     * Search across all content
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'nullable|in:all,villages,products,articles,places,smes'
        ]);

        $search = $request->get('q');
        $type = $request->get('type', 'all');
        $results = [];

        if ($type === 'all' || $type === 'villages') {
            $results['villages'] = Village::where('is_active', true)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->take(5)
                ->get(['id', 'name', 'slug', 'description', 'image_url']);
        }

        if ($type === 'all' || $type === 'products') {
            $results['products'] = Offer::where('is_active', true)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                })
                ->with(['sme.community.village', 'category'])
                ->take(10)
                ->get();
        }

        if ($type === 'all' || $type === 'articles') {
            $results['articles'] = Article::where('is_published', true)
                ->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                })
                ->with(['village', 'community', 'sme', 'place'])
                ->take(10)
                ->get(['id', 'title', 'slug', 'cover_image_url', 'published_at', 'village_id', 'community_id', 'sme_id', 'place_id']);
        }

        if ($type === 'all' || $type === 'places') {
            $results['places'] = Place::where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
                ->with('village')
                ->take(8)
                ->get(['id', 'name', 'slug', 'description', 'image_url', 'village_id']);
        }

        if ($type === 'all' || $type === 'smes') {
            $results['smes'] = Sme::where('is_active', true)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->with(['community.village'])
                ->take(8)
                ->get(['id', 'name', 'slug', 'description', 'type', 'logo_url', 'community_id']);
        }

        return response()->json([
            'search_query' => $search,
            'type' => $type,
            'results' => $results
        ]);
    }

    /**
     * Get system statistics
     */
    public function stats(Request $request)
    {
        $stats = Cache::remember('system_stats', 300, function () {
            return [
                'villages' => [
                    'total' => Village::where('is_active', true)->count(),
                    'with_custom_domains' => Village::whereNotNull('domain')->where('is_active', true)->count(),
                ],
                'communities' => [
                    'total' => Community::where('is_active', true)->count(),
                    'by_village' => Community::where('is_active', true)
                        ->with('village:id,name')
                        ->get()
                        ->groupBy('village.name')
                        ->map->count(),
                ],
                'smes' => [
                    'total' => Sme::where('is_active', true)->count(),
                    'verified' => Sme::where('is_active', true)->where('is_verified', true)->count(),
                    'by_type' => Sme::where('is_active', true)
                        ->selectRaw('type, COUNT(*) as count')
                        ->groupBy('type')
                        ->pluck('count', 'type'),
                ],
                'products' => [
                    'total' => Offer::where('is_active', true)->count(),
                    'featured' => Offer::where('is_active', true)->where('is_featured', true)->count(),
                    'by_availability' => Offer::where('is_active', true)
                        ->selectRaw('availability, COUNT(*) as count')
                        ->groupBy('availability')
                        ->pluck('count', 'availability'),
                ],
                'content' => [
                    'articles' => Article::where('is_published', true)->count(),
                    'places' => Place::count(),
                    'categories' => Category::count(),
                    'tags' => OfferTag::count(),
                ],
            ];
        });

        return response()->json([
            'statistics' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get popular content
     */
    public function popular(Request $request)
    {
        $popular = Cache::remember('popular_content', 600, function () {
            return [
                'products' => Offer::where('is_active', true)
                    ->orderBy('view_count', 'desc')
                    ->with(['sme.community.village', 'category'])
                    ->take(10)
                    ->get(),
                'villages' => Village::where('is_active', true)
                    ->withCount([
                        'communities' => function ($query) {
                            $query->where('is_active', true);
                        }
                    ])
                    ->orderBy('communities_count', 'desc')
                    ->take(5)
                    ->get(['id', 'name', 'slug', 'description', 'image_url']),
                'categories' => Category::withCount(['offers' => function ($query) {
                    $query->where('is_active', true);
                }])
                    ->having('offers_count', '>', 0)
                    ->orderBy('offers_count', 'desc')
                    ->take(10)
                    ->get(),
            ];
        });

        return response()->json([
            'popular' => $popular
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health(Request $request)
    {
        try {
            // Check database connection
            $dbCheck = DB::connection()->getPdo();

            // Check cache
            Cache::put('health_check', 'ok', 60);
            $cacheCheck = Cache::get('health_check') === 'ok';

            // Basic system checks
            $checks = [
                'database' => $dbCheck ? 'ok' : 'error',
                'cache' => $cacheCheck ? 'ok' : 'error',
                'storage' => is_writable(storage_path()) ? 'ok' : 'error',
            ];

            $status = in_array('error', $checks) ? 'error' : 'ok';
            $httpCode = $status === 'ok' ? 200 : 500;

            return response()->json([
                'status' => $status,
                'timestamp' => now()->toISOString(),
                'checks' => $checks,
                'version' => config('app.version', '1.0.0'),
            ], $httpCode);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'timestamp' => now()->toISOString(),
                'error' => 'System health check failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
