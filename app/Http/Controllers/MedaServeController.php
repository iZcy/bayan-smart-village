<?php

// app/Http/Controllers/MediaServeController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaServeController extends Controller
{
    public function thumbnail(Request $request, string $path)
    {
        $width = $request->query('w', 300);
        $height = $request->query('h', 300);
        $quality = $request->query('q', 80);

        // Validate dimensions
        $width = max(50, min(800, (int) $width));
        $height = max(50, min(800, (int) $height));
        $quality = max(10, min(100, (int) $quality));

        $cacheKey = "thumb_{$width}x{$height}_{$quality}_" . md5($path);
        $cachePath = "cache/thumbnails/{$cacheKey}.jpg";

        // Check if cached version exists
        if (Storage::disk('public')->exists($cachePath)) {
            return response(Storage::disk('public')->get($cachePath))
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=31536000'); // 1 year
        }

        // Check if original exists
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        try {
            $originalPath = Storage::disk('public')->path($path);
            $image = Image::make($originalPath);

            // Resize and optimize
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });

            $image->encode('jpg', $quality);

            // Save to cache
            Storage::disk('public')->put($cachePath, $image->getEncoded());

            return response($image->getEncoded())
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=31536000');
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function optimized(Request $request, string $path)
    {
        $quality = $request->query('q', 85);
        $width = $request->query('w');
        $height = $request->query('h');

        // Validate quality
        $quality = max(10, min(100, (int) $quality));

        $cacheKey = "opt_" . ($width ? "w{$width}_" : '') . ($height ? "h{$height}_" : '') . "q{$quality}_" . md5($path);
        $cachePath = "cache/optimized/{$cacheKey}.jpg";

        // Check if cached version exists
        if (Storage::disk('public')->exists($cachePath)) {
            return response(Storage::disk('public')->get($cachePath))
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=31536000');
        }

        // Check if original exists
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        try {
            $originalPath = Storage::disk('public')->path($path);
            $image = Image::make($originalPath);

            // Apply resizing if specified
            if ($width || $height) {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $image->encode('jpg', $quality);

            // Save to cache
            Storage::disk('public')->put($cachePath, $image->getEncoded());

            return response($image->getEncoded())
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=31536000');
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
