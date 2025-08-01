<?php

use App\Http\Controllers\ExternalLinkController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\StuntingCalculatorController;
use App\Http\Controllers\VillagePageController;
use App\Http\Controllers\MediaController;
use App\Http\Middleware\ResolveVillageSubdomain;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Get the base domain from config
$baseDomain = config('app.domain', 'kecamatanbayan.id');

// Define main domain routes function
$mainDomainRoutes = function () use ($baseDomain) {
    // Stunting Calculator Routes
    Route::prefix('stunting-calculator')->name('stunting.')->group(function () {
        Route::get('/', [StuntingCalculatorController::class, 'index'])->name('index');
        Route::post('/calculate', [StuntingCalculatorController::class, 'calculate'])->name('calculate');
    });

    // Short link redirect for apex domain
    Route::get('/l/{slug}', [ExternalLinkController::class, 'redirect'])
        ->name('short-link.redirect');

    // Global Media API routes
    Route::prefix('api/media')->name('api.media.')->group(function () {
        Route::get('/{context}', [MediaController::class, 'getContextMedia'])->name('context');
        Route::get('/{context}/featured', [MediaController::class, 'getFeaturedMedia'])->name('featured');
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::post('/', [MediaController::class, 'store'])->name('store');
        Route::put('/{media}', [MediaController::class, 'update'])->name('update');
        Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
        Route::get('/stats', [MediaController::class, 'stats'])->name('stats');
    });

    // API routes for programmatic access
    Route::prefix('api/links')->name('api.links.')->group(function () {
        Route::get('/', [ExternalLinkController::class, 'index'])->name('index');
        Route::post('/', [ExternalLinkController::class, 'store'])->name('store');
        Route::get('/domain', [ExternalLinkController::class, 'domainLinks'])->name('domain');
        Route::get('/{village}/{slug}/stats', [ExternalLinkController::class, 'stats'])->name('stats');
    });

    // Public product routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [OfferController::class, 'index'])->name('index');
        Route::get('/featured', [OfferController::class, 'featured'])->name('featured');
        Route::get('/categories', [OfferController::class, 'categories'])->name('categories');
        Route::get('/tags', [OfferController::class, 'tags'])->name('tags');
        Route::get('/search', [OfferController::class, 'search'])->name('search');
        Route::get('/stats', [OfferController::class, 'stats'])->name('stats');
        Route::get('/{slug}', [OfferController::class, 'show'])->name('show');
    });

    // Product e-commerce link tracking
    Route::post('/products/{product}/links/{link}/click', [OfferController::class, 'trackLinkClick'])
        ->name('products.link.click');

    // Fallback to Filament admin
    Route::fallback(function () {
        return redirect(filament()->getLoginUrl());
    });
};

// Apply routes to main domain
Route::domain($baseDomain)->group($mainDomainRoutes);

// Apply same routes to reserved subdomains
$reservedSubdomains = ['www', 'mail', 'ftp', 'admin', 'api', 'cdn', 'static'];
foreach ($reservedSubdomains as $subdomain) {
    Route::domain($subdomain . '.' . $baseDomain)->group($mainDomainRoutes);
}

// Development localhost routes (same as main domain for testing)
if (app()->environment('local')) {
    Route::domain('localhost')->group(function () {
        // Global Media API routes for development
        Route::prefix('api/media')->name('api.media.dev.')->group(function () {
            Route::get('/{context}', [MediaController::class, 'getContextMedia'])->name('context');
            Route::get('/{context}/featured', [MediaController::class, 'getFeaturedMedia'])->name('featured');
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::post('/', [MediaController::class, 'store'])->name('store');
            Route::put('/{media}', [MediaController::class, 'update'])->name('update');
            Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
            Route::get('/stats', [MediaController::class, 'stats'])->name('stats');
        });
        
        // Development village routes for testing (simulate a village subdomain)
        Route::get('/', function () {
            // Get a sample village for testing
            $village = \App\Models\Village::first();
            if (!$village) {
                return response('No villages found. Please run database seeders.', 404);
            }
            // Set village in request attributes like the middleware does
            request()->attributes->set('village', $village);
            return app(\App\Http\Controllers\VillagePageController::class)->home();
        })->name('dev.home');
    });
}

// Routes for village subdomains (e.g., village-name.kecamatanbayan.id)
Route::domain('{village}.' . $baseDomain)
    ->middleware([ResolveVillageSubdomain::class])
    ->group(function () {
        // Village homepage
        Route::get('/', [VillagePageController::class, 'home'])->name('village.home');

        // Articles
        Route::prefix('articles')->name('village.articles.')->group(function () {
            Route::get('/', [VillagePageController::class, 'articles'])->name('index');
            Route::get('/{slug}', [VillagePageController::class, 'articleShow'])->name('show');
        });

        // Products (Offers)
        Route::prefix('products')->name('village.products.')->group(function () {
            Route::get('/', [VillagePageController::class, 'products'])->name('index');
            Route::get('/{slug}', [VillagePageController::class, 'productShow'])->name('show');
        });

        // Places
        Route::prefix('places')->name('village.places.')->group(function () {
            Route::get('/', [VillagePageController::class, 'places'])->name('index');
            Route::get('/{slug}', [VillagePageController::class, 'placeShow'])->name('show');
        });

        // SMEs (Businesses)
        Route::prefix('smes')->name('village.smes.')->group(function () {
            Route::get('/', [VillagePageController::class, 'smes'])->name('index');
            Route::get('/{slug}', [VillagePageController::class, 'smeShow'])->name('show');
        });

        // Gallery
        Route::get('/gallery', [VillagePageController::class, 'gallery'])->name('village.gallery');

        // Short link redirect for village subdomains
        Route::get('/l/{slug}', [ExternalLinkController::class, 'redirect'])
            ->name('village.short-link.redirect');

        // Village-specific API
        Route::prefix('api')->name('village.api.')->group(function () {
            // Media endpoints for villages
            Route::prefix('media')->name('media.')->group(function () {
                Route::get('/{context}', [MediaController::class, 'getContextMedia'])->name('context');
                Route::get('/{context}/featured', [MediaController::class, 'getFeaturedMedia'])->name('featured');
                Route::get('/', [MediaController::class, 'index'])->name('index');
                Route::post('/', [MediaController::class, 'store'])->name('store');
                Route::put('/{media}', [MediaController::class, 'update'])->name('update');
                Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
                Route::get('/stats', [MediaController::class, 'stats'])->name('stats');
            });

            // External links
            Route::get('/links', [ExternalLinkController::class, 'domainLinks'])->name('links');

            // Products
            Route::get('/products', [OfferController::class, 'villageProducts'])->name('products');
            Route::get('/products/{slug}', [OfferController::class, 'show'])->name('products.show');
            Route::post('/products/{product}/links/{link}/click', [OfferController::class, 'trackLinkClick'])
                ->name('products.link.click');

            // Places
            Route::get('/places', function (Request $request) {
                $village = $request->attributes->get('village');
                if (!$village) {
                    abort(404, 'Village not found');
                }

                $places = $village->places()->with(['images' => function ($query) {
                    $query->where('is_featured', true)->take(1);
                }])->get();

                return response()->json([
                    'village' => $village->name,
                    'places' => $places->map(function ($place) {
                        return [
                            'id' => $place->id,
                            'name' => $place->name,
                            'slug' => $place->slug,
                            'description' => $place->description,
                            'address' => $place->address,
                            'phone_number' => $place->phone_number,
                            'image_url' => $place->image_url,
                            'latitude' => $place->latitude,
                            'longitude' => $place->longitude,
                            'featured_image' => $place->images->first()?->image_url,
                            'custom_fields' => $place->custom_fields,
                        ];
                    })
                ]);
            })->name('places');

            // Articles
            Route::get('/articles', function (Request $request) {
                $village = $request->attributes->get('village');
                if (!$village) {
                    abort(404, 'Village not found');
                }

                $articles = $village->articles()
                    ->where('is_published', true)
                    ->with(['community', 'sme', 'place'])
                    ->latest('published_at')
                    ->get();

                return response()->json([
                    'village' => $village->name,
                    'articles' => $articles->map(function ($article) {
                        return [
                            'id' => $article->id,
                            'title' => $article->title,
                            'slug' => $article->slug,
                            'content' => $article->content,
                            'cover_image_url' => $article->cover_image_url,
                            'is_featured' => $article->is_featured,
                            'published_at' => $article->published_at,
                            'community' => $article->community ? [
                                'id' => $article->community->id,
                                'name' => $article->community->name,
                                'slug' => $article->community->slug,
                            ] : null,
                            'sme' => $article->sme ? [
                                'id' => $article->sme->id,
                                'name' => $article->sme->name,
                                'slug' => $article->sme->slug,
                            ] : null,
                            'place' => $article->place ? [
                                'id' => $article->place->id,
                                'name' => $article->place->name,
                                'slug' => $article->place->slug,
                            ] : null,
                            'created_at' => $article->created_at,
                        ];
                    })
                ]);
            })->name('articles');

            // Communities
            Route::get('/communities', function (Request $request) {
                $village = $request->attributes->get('village');
                if (!$village) {
                    abort(404, 'Village not found');
                }

                $communities = $village->communities()
                    ->where('is_active', true)
                    ->withCount(['smes' => function ($query) {
                        $query->where('is_active', true);
                    }])
                    ->get();

                return response()->json([
                    'village' => $village->name,
                    'communities' => $communities->map(function ($community) {
                        return [
                            'id' => $community->id,
                            'name' => $community->name,
                            'slug' => $community->slug,
                            'description' => $community->description,
                            'logo_url' => $community->logo_url,
                            'contact_person' => $community->contact_person,
                            'contact_phone' => $community->contact_phone,
                            'contact_email' => $community->contact_email,
                            'smes_count' => $community->smes_count,
                        ];
                    })
                ]);
            })->name('communities');

            // SMEs
            Route::get('/smes', function (Request $request) {
                $village = $request->attributes->get('village');
                if (!$village) {
                    abort(404, 'Village not found');
                }

                $smes = \App\Models\Sme::whereHas('community', function ($query) use ($village) {
                    $query->where('village_id', $village->id);
                })
                    ->where('is_active', true)
                    ->with(['community', 'place'])
                    ->withCount(['offers' => function ($query) {
                        $query->where('is_active', true);
                    }])
                    ->get();

                return response()->json([
                    'village' => $village->name,
                    'smes' => $smes->map(function ($sme) {
                        return [
                            'id' => $sme->id,
                            'name' => $sme->name,
                            'slug' => $sme->slug,
                            'description' => $sme->description,
                            'type' => $sme->type,
                            'logo_url' => $sme->logo_url,
                            'contact_phone' => $sme->contact_phone,
                            'contact_email' => $sme->contact_email,
                            'is_verified' => $sme->is_verified,
                            'community' => [
                                'id' => $sme->community->id,
                                'name' => $sme->community->name,
                                'slug' => $sme->community->slug,
                            ],
                            'place' => $sme->place ? [
                                'id' => $sme->place->id,
                                'name' => $sme->place->name,
                                'slug' => $sme->place->slug,
                            ] : null,
                            'offers_count' => $sme->offers_count,
                        ];
                    })
                ]);
            })->name('smes');

            // Village information
            Route::get('/info', function (Request $request) {
                $village = $request->attributes->get('village');
                if (!$village) {
                    abort(404, 'Village not found');
                }

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
                        'settings' => $village->settings,
                        'established_at' => $village->established_at,
                    ],
                    'statistics' => [
                        'total_communities' => $village->communities()->where('is_active', true)->count(),
                        'total_places' => $village->places()->count(),
                        'total_smes' => \App\Models\Sme::whereHas('community', function ($query) use ($village) {
                            $query->where('village_id', $village->id);
                        })->where('is_active', true)->count(),
                        'total_products' => \App\Models\Offer::whereHas('sme.community', function ($query) use ($village) {
                            $query->where('village_id', $village->id);
                        })->where('is_active', true)->count(),
                        'total_links' => $village->externalLinks()->where('is_active', true)->count(),
                        'total_articles' => $village->articles()->where('is_published', true)->count(),
                        'total_images' => $village->images()->count(),
                        'total_media' => \App\Models\Media::where('village_id', $village->id)->where('is_active', true)->count(),
                    ]
                ]);
            })->name('info');
        });
    });

// Image upload routes (add these to your existing routes)
Route::middleware(['auth'])->prefix('admin/uploads')->name('admin.uploads.')->group(function () {
    Route::post('/image', [App\Http\Controllers\ImageUploadController::class, 'upload'])->name('image');
    Route::post('/images', [App\Http\Controllers\ImageUploadController::class, 'uploadMultiple'])->name('images');
    Route::delete('/image', [App\Http\Controllers\ImageUploadController::class, 'delete'])->name('image.delete');
    Route::post('/thumbnail', [App\Http\Controllers\ImageUploadController::class, 'generateThumbnail'])->name('thumbnail');
});

// Handle direct admin access attempts that don't match Filament routes
Route::prefix('admin')->group(function () {
    Route::fallback(function () {
        return response()->view('errors.admin-404', [], 404);
    });
});

// Public image serving routes (for optimization)
Route::prefix('media')->name('media.')->group(function () {
    Route::get('/thumbnail/{path}', [App\Http\Controllers\MediaServeController::class, 'thumbnail'])
        ->where('path', '.*')
        ->name('thumbnail');
    Route::get('/optimized/{path}', [App\Http\Controllers\MediaServeController::class, 'optimized'])
        ->where('path', '.*')
        ->name('optimized');
});

// Audio serving route (for proper range request handling)
Route::get('/audio/{filename}', function($filename) {
    $path = public_path('audio/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    $size = filesize($path);
    $length = $size;
    $start = 0;
    $end = $size - 1;
    
    $headers = [
        'Content-Type' => 'audio/mpeg',
        'Accept-Ranges' => 'bytes',
        'Content-Length' => $size,
        'Cache-Control' => 'public, max-age=3600',
    ];
    
    // Handle range requests for audio streaming
    if (request()->hasHeader('Range')) {
        $range = request()->header('Range');
        if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            $start = intval($matches[1]);
            $end = $matches[2] ? intval($matches[2]) : $size - 1;
            $length = $end - $start + 1;
            
            $headers['Content-Range'] = "bytes $start-$end/$size";
            $headers['Content-Length'] = $length;
            
            $status = 206; // Partial Content
        }
    } else {
        $status = 200;
    }
    
    $stream = fopen($path, 'rb');
    fseek($stream, $start);
    $content = fread($stream, $length);
    fclose($stream);
    
    return response($content, $status, $headers);
})->where('filename', '.*')->name('audio.serve');

// Handle custom domains dynamically
try {
    $villagesWithCustomDomains = \App\Models\Village::whereNotNull('domain')
        ->where('is_active', true)
        ->get();

    foreach ($villagesWithCustomDomains as $village) {
        Route::domain($village->domain)
            ->middleware([ResolveVillageSubdomain::class])
            ->group(function () use ($village) {
                // Custom domain routes - same as subdomain routes
                Route::get('/', [VillagePageController::class, 'home'])->name("custom.{$village->slug}.home");

                // Articles
                Route::prefix('articles')->name("custom.{$village->slug}.articles.")->group(function () {
                    Route::get('/', [VillagePageController::class, 'articles'])->name('index');
                    Route::get('/{slug}', [VillagePageController::class, 'articleShow'])->name('show');
                });

                // Products
                Route::prefix('products')->name("custom.{$village->slug}.products.")->group(function () {
                    Route::get('/', [VillagePageController::class, 'products'])->name('index');
                    Route::get('/{slug}', [VillagePageController::class, 'productShow'])->name('show');
                });

                // Places
                Route::prefix('places')->name("custom.{$village->slug}.places.")->group(function () {
                    Route::get('/', [VillagePageController::class, 'places'])->name('index');
                    Route::get('/{slug}', [VillagePageController::class, 'placeShow'])->name('show');
                });

                // SMEs (Businesses)
                Route::prefix('smes')->name("custom.{$village->slug}.smes.")->group(function () {
                    Route::get('/', [VillagePageController::class, 'smes'])->name('index');
                    Route::get('/{slug}', [VillagePageController::class, 'smeShow'])->name('show');
                });

                Route::get('/gallery', [VillagePageController::class, 'gallery'])->name("custom.{$village->slug}.gallery");

                // Short link redirect for custom domains
                Route::get('/l/{slug}', [ExternalLinkController::class, 'redirect'])
                    ->name("custom.{$village->slug}.short-link.redirect");

                // API for custom domains (including media)
                Route::prefix('api')->name("custom.{$village->slug}.api.")->group(function () {
                    // Media endpoints
                    Route::prefix('media')->name('media.')->group(function () {
                        Route::get('/{context}', [MediaController::class, 'getContextMedia'])->name('context');
                        Route::get('/{context}/featured', [MediaController::class, 'getFeaturedMedia'])->name('featured');
                        Route::get('/', [MediaController::class, 'index'])->name('index');
                        Route::post('/', [MediaController::class, 'store'])->name('store');
                        Route::put('/{media}', [MediaController::class, 'update'])->name('update');
                        Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
                        Route::get('/stats', [MediaController::class, 'stats'])->name('stats');
                    });

                    Route::get('/links', [ExternalLinkController::class, 'domainLinks'])->name('links');
                    Route::get('/products', [OfferController::class, 'villageProducts'])->name('products');
                    Route::get('/products/{slug}', [OfferController::class, 'show'])->name('products.show');
                    Route::post('/products/{product}/links/{link}/click', [OfferController::class, 'trackLinkClick'])
                        ->name('products.link.click');
                });
            });
    }
} catch (\Exception $e) {
    // Handle case where database is not yet migrated
    Log::info('Database not ready for custom domain routes: ' . $e->getMessage());
}
