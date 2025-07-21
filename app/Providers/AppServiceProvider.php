<?php

namespace App\Providers;

use App\Models\Image;
use App\Models\Media;
use App\Observers\ImageObserver;
use App\Observers\MediaObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the ImageUploadService
        $this->app->singleton(\App\Services\ImageUploadService::class);
    }

    public function boot(): void
    {
        // Register observers
        Image::observe(ImageObserver::class);
        Media::observe(MediaObserver::class);
    }
}
