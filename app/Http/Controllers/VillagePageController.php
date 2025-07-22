<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\Article;
use App\Models\Offer;
use App\Models\Place;
use App\Models\Image;
use App\Models\ExternalLink;
use App\Models\Category;
use App\Models\Sme;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VillagePageController extends Controller
{
    public function home(Request $request)
    {
        $village = $request->attributes->get('village');

        // Get featured articles
        $featuredArticles = Article::where('village_id', $village->id)
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with(['community', 'sme', 'place'])
            ->latest('published_at')
            ->take(3)
            ->get();

        // Get featured products (actual Offers from SMEs)
        $featuredProducts = Offer::whereHas('sme.community', function ($query) use ($village) {
            $query->where('village_id', $village->id);
        })
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with(['sme.community', 'category', 'tags'])
            ->latest()
            ->take(6)
            ->get();

        // Enhanced Places data with service/product distinction
        // Tourism Places = Places that offer services (category type: "service")
        // SME Places = Places that offer products (category type: "product")
        $featuredPlaces = Place::where('village_id', $village->id)
            ->with([
                'category',
                'images' => function ($query) {
                    $query->where('is_featured', true)->take(1);
                },
                // Include SMEs if this is a product business location
                'smes' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['offers' => function ($offerQuery) {
                            $offerQuery->where('is_active', true)->take(3);
                        }]);
                }
            ])
            ->whereHas('category') // Only places with categories
            ->latest()
            ->take(12) // Increased to support both tourism and SME sections
            ->get()
            ->map(function ($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'slug' => $place->slug,
                    'description' => $place->description,
                    'address' => $place->address,
                    'phone_number' => $place->phone_number,
                    'latitude' => $place->latitude,
                    'longitude' => $place->longitude,
                    'image_url' => $place->image_url ?: ($place->images->first()?->image_url),
                    'custom_fields' => $place->custom_fields,
                    'category' => $place->category ? [
                        'id' => $place->category->id,
                        'name' => $place->category->name,
                        'type' => $place->category->type, // "service" or "product"
                        'icon' => $place->category->icon,
                        'description' => $place->category->description,
                    ] : null,
                    // Additional info for SME places (product businesses)
                    'smes_count' => $place->smes->count(),
                    'products_count' => $place->smes->sum(function ($sme) {
                        return $sme->offers->count();
                    }),
                    'business_type' => $place->category?->type === 'product' ? 'SME Business' : 'Tourism Service',
                    'created_at' => $place->created_at,
                    'updated_at' => $place->updated_at,
                ];
            });

        $featuredImages = Image::where('village_id', $village->id)
            ->where('is_featured', true)
            ->with(['place', 'community', 'sme'])
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return Inertia::render('Village/Home', [
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
                'settings' => $village->settings,
            ],
            'featuredArticles' => $featuredArticles,
            'featuredProducts' => $featuredProducts, // Actual products from SMEs
            'featuredPlaces' => $featuredPlaces, // Places categorized by service/product type
            'featuredImages' => $featuredImages,
            // Additional context for frontend
            'placeStats' => [
                'tourism_places_count' => $featuredPlaces->where('category.type', 'service')->count(),
                'sme_places_count' => $featuredPlaces->where('category.type', 'product')->count(),
                'total_places_count' => $featuredPlaces->count(),
            ]
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
            ->with([
                'category',
                'images' => function ($query) {
                    $query->where('is_featured', true)->take(1);
                },
                // Include related SMEs for product business places
                'smes' => function ($query) {
                    $query->where('is_active', true)
                        ->withCount(['offers' => function ($offerQuery) {
                            $offerQuery->where('is_active', true);
                        }]);
                }
            ]);

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

        // Get available categories separated by type
        $categories = Category::where('village_id', $village->id)
            ->whereHas('places')
            ->withCount('places')
            ->get()
            ->groupBy('type');

        return Inertia::render('Village/Places/Index', [
            'village' => $village,
            'places' => $places,
            'categories' => $categories->flatten(), // Flatten for the filter dropdown
            'categoryGroups' => $categories, // Grouped for display sections
            'filters' => [
                'search' => $request->get('search', ''),
                'category' => $request->get('category', ''),
                'type' => $request->get('type', ''),
            ],
            'placeTypes' => [
                'service' => [
                    'label' => 'Tourism & Services',
                    'description' => 'Hotels, restaurants, tour guides, and other service providers',
                    'icon' => '🏞️',
                    'count' => $places->where('category.type', 'service')->count()
                ],
                'product' => [
                    'label' => 'Product Businesses',
                    'description' => 'Shops, craft centers, markets, and product manufacturers',
                    'icon' => '🏪',
                    'count' => $places->where('category.type', 'product')->count()
                ]
            ]
        ]);
    }

    public function placeShow(Request $request)
    {
        $slug = last(explode('/', $request->getRequestUri()));

        $village = $request->attributes->get('village');

        $place = Place::where('village_id', $village->id)
            ->where('slug', $slug)
            ->with([
                'category',
                'images' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'smes' => function ($query) {
                    $query->where('is_active', true)
                        ->with([
                            'community',
                            'offers' => function ($offerQuery) {
                                $offerQuery->where('is_active', true)
                                    ->orderBy('is_featured', 'desc')
                                    ->take(6);
                            }
                        ]);
                },
                'articles' => function ($query) {
                    $query->where('is_published', true)->latest('published_at')->take(3);
                }
            ])
            ->firstOrFail();

        // Additional context based on place type
        $placeContext = [
            'is_service_place' => $place->category?->type === 'service',
            'is_product_place' => $place->category?->type === 'product',
            'service_type' => $place->category?->type === 'service' ? $place->category->name : null,
            'business_count' => $place->smes->count(),
            'product_count' => $place->smes->sum(function ($sme) {
                return $sme->offers->count();
            }),
        ];

        return Inertia::render('Village/Places/Show', [
            'village' => $village,
            'place' => $place,
            'placeContext' => $placeContext,
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

        if ($request->filled('type')) {
            // Filter by place type (service/product)
            $query->whereHas('place.category', function ($q) use ($request) {
                $q->where('type', $request->get('type'));
            });
        }

        $images = $query->orderBy('sort_order')->paginate(24);

        // Get available places for filtering, grouped by type
        $places = Place::where('village_id', $village->id)
            ->whereHas('images')
            ->with('category')
            ->select('id', 'name', 'category_id')
            ->get()
            ->groupBy(function ($place) {
                return $place->category?->type ?? 'other';
            });

        return Inertia::render('Village/Gallery', [
            'village' => $village,
            'images' => $images,
            'places' => $places->flatten(), // For the filter dropdown
            'placeGroups' => $places, // Grouped for organized display
            'filters' => [
                'search' => $request->get('search', ''),
                'place' => $request->get('place', ''),
                'type' => $request->get('type', ''),
            ],
        ]);
    }

    public function smes(Request $request)
    {
        $village = $request->attributes->get('village');

        $query = Sme::whereHas('community', function ($q) use ($village) {
            $q->where('village_id', $village->id);
        })
            ->where('is_active', true)
            ->with([
                'community',
                'place',
                'offers' => function ($query) {
                    $query->where('is_active', true)->take(3);
                }
            ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('place')) {
            $query->where('place_id', $request->get('place'));
        }

        // Sorting
        $sort = $request->get('sort', 'featured');
        switch ($sort) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'verified':
                $query->orderBy('is_verified', 'desc');
                break;
            default: // featured
                $query->orderBy('is_verified', 'desc')->orderBy('name');
                break;
        }

        $smes = $query->paginate(12);

        // Get available places for filtering
        $places = Place::where('village_id', $village->id)
            ->whereHas('smes', function ($query) {
                $query->where('is_active', true);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Village/SMEs/Index', [
            'village' => $village,
            'smes' => $smes,
            'places' => $places,
            'filters' => [
                'search' => $request->get('search', ''),
                'type' => $request->get('type', ''),
                'place' => $request->get('place', ''),
                'sort' => $sort,
            ],
        ]);
    }

    public function smeShow(Request $request)
    {
        $slug = last(explode('/', $request->getRequestUri()));
        $village = $request->attributes->get('village');

        $sme = Sme::whereHas('community', function ($q) use ($village) {
            $q->where('village_id', $village->id);
        })
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'community',
                'place.category',
                'offers' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['category', 'tags', 'images'])
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('name');
                },
                'images' => function ($query) {
                    $query->orderBy('sort_order');
                }
            ])
            ->firstOrFail();

        return Inertia::render('Village/SMEs/Show', [
            'village' => $village,
            'sme' => $sme,
        ]);
    }
}
