<?php
// routes/web.php

use App\Http\Controllers\ExternalLinkController;
use Illuminate\Support\Facades\Route;

// Subdomain routes for external links with /l/ prefix
Route::domain('{subdomain}.' . config('app.domain'))->group(function () {
    // Only /l/{slug} format is supported now
    Route::get('/l/{slug}', [ExternalLinkController::class, 'redirect'])
        ->name('external_link.subdomain.l');

    // Optional: Redirect root to main place page or 404
    Route::get('/', function (string $subdomain) {
        // You could redirect to place page or show 404
        abort(404, 'Please use the full link format: ' . $subdomain . '.' . config('app.domain') . '/l/{slug}');
    })->name('external_link.subdomain.root');
});

// Fallback routes (for development/testing)
Route::get('/fallback/{subdomain}/l/{slug}', [ExternalLinkController::class, 'redirect'])
    ->name('external_link.fallback');
