<?php

use App\Http\Controllers\ExternalLinkController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VillagePageController;
use App\Http\Middleware\ResolveVillageSubdomain;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Get the base domain from config
$baseDomain = config('app.domain', 'kecamatanbayan.id');

// Routes for the main/apex domain
Route::domain($baseDomain)->group(function () {
    // Short link redirect for apex domain
    Route::get('/l/{slug}', [ExternalLinkController::class, 'redirect'])
        ->name('short-link.redirect');

    // API routes for programmatic access
    Route::prefix('api/links')->name('api.links.')->group(function () {
        Route::get('/', [ExternalLinkController::class, 'index'])->name('index');
        Route::post('/', [ExternalLinkController::class, 'store'])->name('store');
        Route::get('/domain', [ExternalLinkController::class, 'domainLinks'])->name('domain');
        Route::get('/{village}/{slug}/stats', [ExternalLinkController::class, 'stats'])->name('stats');
    });

    // Public product routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/featured', [ProductController::class, 'featured'])->name('featured');
        Route::get('/categories', [ProductController::class, 'categories'])->name('categories');
        Route::get('/tags', [ProductController::class, 'tags'])->name('tags');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::get('/stats', [ProductController::class, 'stats'])->name('stats');
        Route::get('/{slug}', [ProductController::class, 'show'])->name('show');
    });

    // Product e-commerce link tracking
    Route::post('/products/{product}/links/{link}/click', [ProductController::class, 'trackLinkClick'])
        ->name('products.link.click');
});

// Routes for village subdomains (e.g., village-name.kecamatanbayan.id)
Route::domain('{village}.' . $baseDomain)
    ->middleware([ResolveVillageSubdomain::class])
    ->group(function () {
        // Village homepage
        Route::get('/', [VillagePageController::class, 'home'])->name('village.home');

        // Articles - Updated to use slugs
        Route::get('/articles', [VillagePageController::class, 'articles'])->name('village.articles');
        Route::get('/articles/{slug}', [VillagePageController::class, 'articleShow'])->name('village.articles.show');

        // Products - Already using slugs
        Route::get('/products', [VillagePageController::class, 'products'])->name('village.products');
        Route::get('/products/{slug}', [VillagePageController::class, 'productShow'])->name('village.products.show');

        // Places - Updated to use slugs
        Route::get('/places', [VillagePageController::class, 'places'])->name('village.places');
        Route::get('/places/{slug}', [VillagePageController::class, 'placeShow'])->name('village.places.show');

        // Gallery
        Route::get('/gallery', [VillagePageController::class, 'gallery'])->name('village.gallery');

        // Short link redirect for village subdomains
        Route::get('/l/{slug}', function (Request $request, $slug) {
            Log::info('Route parameters debug', [
                'slug_from_route' => $slug,
                'path' => $request->path(),
                'all_route_params' => $request->route()->parameters(),
                'route_param_names' => array_keys($request->route()->parameters()),
            ]);

            return app(ExternalLinkController::class)->redirect($request, $slug);
        })->name('village.short-link.redirect');

        // Village-specific API
        Route::prefix('api')->name('village.api.')->group(function () {
            Route::get('/links', [ExternalLinkController::class, 'domainLinks'])->name('links');
            Route::get('/products', [ProductController::class, 'villageProducts'])->name('products');
            Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
            Route::post('/products/{product}/links/{link}/click', [ProductController::class, 'trackLinkClick'])->name('products.link.click');

            Route::get('/places', function (Request $request) {
                $village = $request->attributes->get('village');
                if (!$village) {
                    abort(404, 'Village not found');
                }

                $places = $village->places()->with('category')->get();

                return response()->json([
                    'village' => $village->name,
                    'places' => $places->map(function ($place) {
                        return [
                            'id' => $place->id,
                            'name' => $place->name,
                            'slug' => $place->slug,
                            'description' => $place->description,
                            'category' => $place->category->name,
                            'phone_number' => $place->phone_number,
                            'image_url' => $place->image_url,
                        ];
                    })
                ]);
            })->name('places');

            Route::get('/articles', function (Request $request) {
                $village = $request->attributes->get('village');
                if (!$village) {
                    abort(404, 'Village not found');
                }

                $articles = $village->articles()->with('place')->latest()->get();

                return response()->json([
                    'village' => $village->name,
                    'articles' => $articles->map(function ($article) {
                        return [
                            'id' => $article->id,
                            'title' => $article->title,
                            'slug' => $article->slug,
                            'excerpt' => $article->excerpt,
                            'cover_image_url' => $article->cover_image_url,
                            'place' => $article->place ? [
                                'id' => $article->place->id,
                                'name' => $article->place->name,
                                'slug' => $article->place->slug,
                            ] : null,
                            'reading_time' => $article->reading_time,
                            'created_at' => $article->created_at,
                        ];
                    })
                ]);
            })->name('articles');

            Route::get('/info', function (Request $request) {
                $village = $request->attributes->get('village');
                if (!$village) {
                    abort(404, 'Village not found');
                }

                return response()->json([
                    'village' => [
                        'name' => $village->name,
                        'slug' => $village->slug,
                        'description' => $village->description,
                        'domain' => $village->full_domain,
                        'phone_number' => $village->phone_number,
                        'email' => $village->email,
                        'address' => $village->address,
                        'settings' => $village->settings,
                        'established_at' => $village->established_at,
                    ],
                    'statistics' => [
                        'total_places' => $village->places()->count(),
                        'total_links' => $village->externalLinks()->count(),
                        'active_links' => $village->activeExternalLinks()->count(),
                        'total_articles' => $village->articles()->count(),
                    ]
                ]);
            })->name('info');
        });
    });

// Handle custom domains dynamically
try {
    $villagesWithCustomDomains = \App\Models\Village::whereNotNull('domain')->active()->get();

    foreach ($villagesWithCustomDomains as $village) {
        Route::domain($village->domain)
            ->middleware([ResolveVillageSubdomain::class])
            ->group(function () use ($village) {
                // Custom domain routes - same as subdomain routes but with slugs
                Route::get('/', [VillagePageController::class, 'home'])->name("custom.{$village->slug}.home");
                Route::get('/articles', [VillagePageController::class, 'articles'])->name("custom.{$village->slug}.articles");
                Route::get('/articles/{slug}', [VillagePageController::class, 'articleShow'])->name("custom.{$village->slug}.articles.show");
                Route::get('/products', [VillagePageController::class, 'products'])->name("custom.{$village->slug}.products");
                Route::get('/products/{slug}', [VillagePageController::class, 'productShow'])->name("custom.{$village->slug}.products.show");
                Route::get('/places', [VillagePageController::class, 'places'])->name("custom.{$village->slug}.places");
                Route::get('/places/{slug}', [VillagePageController::class, 'placeShow'])->name("custom.{$village->slug}.places.show");
                Route::get('/gallery', [VillagePageController::class, 'gallery'])->name("custom.{$village->slug}.gallery");

                // Short link redirect for custom domains
                Route::get('/l/{slug}', [ExternalLinkController::class, 'redirect'])
                    ->name("custom.{$village->slug}.short-link.redirect");

                // API for custom domains
                Route::get('/api/links', [ExternalLinkController::class, 'domainLinks'])
                    ->name("custom.{$village->slug}.api.links");
            });
    }
} catch (\Exception $e) {
    // Handle case where database is not yet migrated
}
