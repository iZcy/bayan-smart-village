<?php

use App\Http\Controllers\ExternalLinkController;
use App\Http\Middleware\ResolveVillageSubdomain;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Get the base domain from config
$baseDomain = config('app.domain', 'kecamatanbayan.id');

// Routes for the main/apex domain
Route::domain($baseDomain)->group(function () {
    // Main website routes
    Route::get('/', function () {
        return view('welcome');
    });

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

    // Testing/debugging routes (remove in production)
    Route::get('/test/links', function () {
        $links = \App\Models\ExternalLink::with('village')->get();
        return response()->json([
            'total_links' => $links->count(),
            'apex_links' => $links->whereNull('village_id')->count(),
            'village_links' => $links->whereNotNull('village_id')->count(),
            'links' => $links->map(function ($link) {
                return [
                    'id' => $link->id,
                    'label' => $link->label,
                    'slug' => $link->slug,
                    'village' => $link->village?->name,
                    'short_url' => $link->subdomain_url,
                    'target_url' => $link->formatted_url,
                ];
            })
        ]);
    });

    Route::get('/test/villages', function () {
        $villages = \App\Models\Village::active()->get();
        return response()->json([
            'total_villages' => $villages->count(),
            'villages' => $villages->map(function ($village) {
                return [
                    'id' => $village->id,
                    'name' => $village->name,
                    'slug' => $village->slug,
                    'domain' => $village->full_domain,
                    'url' => $village->url,
                    'places_count' => $village->places()->count(),
                    'links_count' => $village->externalLinks()->count(),
                ];
            })
        ]);
    });

    // Debug route for apex domain
    Route::get('/debug/{slug}', function (Request $request, $slug) {
        return response()->json([
            'debug_info' => [
                'host' => $request->getHost(),
                'requested_slug' => $slug,
                'is_apex_domain' => true,
                'village_from_middleware' => null,
                'all_matching_slugs' => \App\Models\ExternalLink::where('slug', $slug)->get()->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'village_id' => $l->village_id,
                        'village_name' => $l->village?->name,
                        'is_active' => $l->is_active,
                        'target_url' => $l->formatted_url ?? $l->url,
                    ];
                }),
            ]
        ]);
    });
});

// Routes for village subdomains (e.g., village-name.kecamatanbayan.id)
Route::domain('{village}.' . $baseDomain)
    ->middleware([ResolveVillageSubdomain::class])
    ->group(function () {
        // Simple test route WITHOUT middleware to check domain routing
        Route::get('/simple-test', function ($village) {
            return response()->json([
                'message' => 'Domain routing works!',
                'detected_village_param' => $village,
                'host' => request()->getHost(),
                'path' => request()->path(),
            ]);
        });
        // Village homepage
        Route::get('/', function (Request $request) {
            // Get village from request (set by middleware)
            $village = $request->attributes->get('village');

            if (!$village) {
                abort(404, 'Village not found');
            }

            return response()->json([
                'message' => "Welcome to {$village->name}",
                'village' => [
                    'name' => $village->name,
                    'slug' => $village->slug,
                    'description' => $village->description,
                    'domain' => $village->full_domain,
                    'places_count' => $village->places()->count(),
                    'links_count' => $village->activeExternalLinks()->count(),
                ]
            ]);
        })->name('village.home');

        // Short link redirect for village subdomains
        Route::get('/l/{slug}', function (Request $request, $slug) {
            // Debug the parameters
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
                            'description' => $place->description,
                            'category' => $place->category->name,
                            'phone_number' => $place->phone_number,
                            'image_url' => $place->image_url,
                        ];
                    })
                ]);
            })->name('places');

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

        // Test routes for villages (remove in production)
        Route::get('/test', function (Request $request) {
            $village = $request->attributes->get('village');

            if (!$village) {
                return response()->json(['error' => 'Village not found'], 404);
            }

            $links = $village->externalLinks;

            return response()->json([
                'village' => $village->name,
                'domain' => request()->getHost(),
                'detected_village' => $village->slug,
                'links' => $links->map(function ($link) {
                    return [
                        'label' => $link->label,
                        'slug' => $link->slug,
                        'short_url' => $link->subdomain_url,
                        'target_url' => $link->formatted_url,
                        'click_count' => $link->click_count,
                    ];
                })
            ]);
        });

        // Add test/links route for village subdomains
        Route::get('/test/links', function (Request $request) {
            $village = $request->attributes->get('village');

            if (!$village) {
                return response()->json(['error' => 'Village not found'], 404);
            }

            $links = $village->externalLinks()->with('village')->get();

            return response()->json([
                'village' => $village->name,
                'domain' => request()->getHost(),
                'detected_village' => $village->slug,
                'total_links' => $links->count(),
                'links' => $links->map(function ($link) {
                    return [
                        'id' => $link->id,
                        'label' => $link->label,
                        'slug' => $link->slug,
                        'village' => $link->village?->name,
                        'short_url' => $link->subdomain_url,
                        'target_url' => $link->formatted_url,
                        'click_count' => $link->click_count,
                    ];
                })
            ]);
        });

        // Debug route for redirect issues
        Route::get('/debug/{slug}', function (Request $request, $slug) {
            $village = $request->attributes->get('village');

            $linkQuery = \App\Models\ExternalLink::where('slug', $slug);

            if ($village) {
                $link = $linkQuery->where('village_id', $village->id)->active()->first();
            } else {
                $link = $linkQuery->whereNull('village_id')->active()->first();
            }

            return response()->json([
                'debug_info' => [
                    'host' => $request->getHost(),
                    'requested_slug' => $slug,
                    'village_from_middleware' => $village ? [
                        'id' => $village->id,
                        'name' => $village->name,
                        'slug' => $village->slug,
                    ] : null,
                    'link_found' => $link ? [
                        'id' => $link->id,
                        'label' => $link->label,
                        'slug' => $link->slug,
                        'village_id' => $link->village_id,
                        'is_active' => $link->is_active,
                        'target_url' => $link->formatted_url,
                        'url_raw' => $link->url,
                    ] : null,
                    'all_matching_slugs' => \App\Models\ExternalLink::where('slug', $slug)->get()->map(function ($l) {
                        return [
                            'id' => $l->id,
                            'village_id' => $l->village_id,
                            'village_name' => $l->village?->name,
                            'is_active' => $l->is_active,
                            'target_url' => $l->formatted_url,
                        ];
                    }),
                ]
            ]);
        });
    });

// Handle custom domains dynamically
// Note: In production, you might want to cache this or handle it differently
try {
    $villagesWithCustomDomains = \App\Models\Village::whereNotNull('domain')->active()->get();

    foreach ($villagesWithCustomDomains as $village) {
        Route::domain($village->domain)
            ->middleware([ResolveVillageSubdomain::class])
            ->group(function () use ($village) {
                // Custom domain homepage
                Route::get('/', function () use ($village) {
                    return response()->json([
                        'message' => "Welcome to {$village->name} (Custom Domain)",
                        'village' => [
                            'name' => $village->name,
                            'slug' => $village->slug,
                            'custom_domain' => $village->domain,
                            'places_count' => $village->places()->count(),
                            'links_count' => $village->activeExternalLinks()->count(),
                        ]
                    ]);
                })->name("custom.{$village->slug}.home");

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
    // This prevents errors during initial setup
}
