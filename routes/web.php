<?php
// routes/web.php (add these routes)

use App\Http\Controllers\ExternalLinkController;
use Illuminate\Support\Facades\Route;

// Short link redirect route (should be in your subdomain route group)
Route::get('/l/{slug}', [ExternalLinkController::class, 'redirect'])
    ->name('short-link.redirect');

// Optional: API routes for programmatic access
Route::prefix('api/links')->name('api.links.')->group(function () {
    Route::get('/', [ExternalLinkController::class, 'index'])->name('index');
    Route::post('/', [ExternalLinkController::class, 'store'])->name('store');
    Route::get('/{subdomain}/{slug}/stats', [ExternalLinkController::class, 'stats'])->name('stats');
});
