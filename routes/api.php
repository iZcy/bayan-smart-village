<?php
// routes/api.php (alternative placement for API routes)

use App\Http\Controllers\ExternalLinkController;
use Illuminate\Support\Facades\Route;

Route::prefix('links')->name('links.')->group(function () {
    Route::get('/', [ExternalLinkController::class, 'index']);
    Route::post('/', [ExternalLinkController::class, 'store']);
    Route::get('/{subdomain}/{slug}/stats', [ExternalLinkController::class, 'stats']);
});
