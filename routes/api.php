<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\ExternalLinkController;
use App\Http\Controllers\OfferController;
use App\Http\Middleware\ApiRateLimit;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Support\Facades\Route;

// Apply CORS and rate limiting to all API routes
Route::middleware([CorsMiddleware::class, ApiRateLimit::class])->group(function () {

    // Health check (no rate limiting)
    Route::get('/health', [ApiController::class, 'health'])->name('api.health')->withoutMiddleware([ApiRateLimit::class]);

    // General API endpoints
    Route::prefix('v1')->name('api.v1.')->group(function () {

        // Villages
        Route::get('/villages', [ApiController::class, 'villages'])->name('villages');
        Route::get('/villages/{slug}', [ApiController::class, 'village'])->name('village');

        // Products (Global)
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [OfferController::class, 'index'])->name('index');
            Route::get('/featured', [OfferController::class, 'featured'])->name('featured');
            Route::get('/categories', [OfferController::class, 'categories'])->name('categories');
            Route::get('/tags', [OfferController::class, 'tags'])->name('tags');
            Route::get('/search', [OfferController::class, 'search'])->name('search');
            Route::get('/stats', [OfferController::class, 'stats'])->name('stats');
            Route::get('/{slug}', [OfferController::class, 'show'])->name('show');
        });

        // External Links (Global)
        Route::prefix('links')->name('links.')->group(function () {
            Route::get('/', [ExternalLinkController::class, 'index'])->name('index');
            Route::post('/', [ExternalLinkController::class, 'store'])->name('store');
            Route::get('/{village}/{slug}/stats', [ExternalLinkController::class, 'stats'])->name('stats');
        });

        // Search
        Route::get('/search', [ApiController::class, 'search'])->name('search');

        // Popular content
        Route::get('/popular', [ApiController::class, 'popular'])->name('popular');

        // System statistics
        Route::get('/stats', [ApiController::class, 'stats'])->name('stats');

        // Product e-commerce link tracking
        Route::post('/products/{product}/links/{link}/click', [OfferController::class, 'trackLinkClick'])
            ->name('products.link.click');
    });

    // Legacy API endpoints (for backward compatibility)
    Route::prefix('links')->name('api.links.')->group(function () {
        Route::get('/', [ExternalLinkController::class, 'index'])->name('index');
        Route::post('/', [ExternalLinkController::class, 'store'])->name('store');
        Route::get('/domain', [ExternalLinkController::class, 'domainLinks'])->name('domain');
        Route::get('/{village}/{slug}/stats', [ExternalLinkController::class, 'stats'])->name('stats');
    });

    Route::prefix('products')->name('api.products.')->group(function () {
        Route::get('/', [OfferController::class, 'index'])->name('index');
        Route::get('/featured', [OfferController::class, 'featured'])->name('featured');
        Route::get('/categories', [OfferController::class, 'categories'])->name('categories');
        Route::get('/tags', [OfferController::class, 'tags'])->name('tags');
        Route::get('/search', [OfferController::class, 'search'])->name('search');
        Route::get('/stats', [OfferController::class, 'stats'])->name('stats');
        Route::get('/{slug}', [OfferController::class, 'show'])->name('show');
        Route::post('/{product}/links/{link}/click', [OfferController::class, 'trackLinkClick'])
            ->name('link.click');
    });
});
