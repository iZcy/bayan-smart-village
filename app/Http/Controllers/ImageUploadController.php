<?php

// app/Http/Controllers/ImageUploadController.php
namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImageUploadController extends Controller
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
            'directory' => 'nullable|string',
            'resize_width' => 'nullable|integer|min:50|max:2000',
            'resize_height' => 'nullable|integer|min:50|max:2000',
            'quality' => 'nullable|integer|min:10|max:100',
        ]);

        try {
            $directory = $request->input('directory', 'uploads');

            $resizeOptions = [];
            if ($request->filled('resize_width') || $request->filled('resize_height')) {
                $resizeOptions = [
                    'width' => $request->input('resize_width'),
                    'height' => $request->input('resize_height'),
                    'quality' => $request->input('quality', 85),
                    'maintain_aspect' => true,
                    'prevent_upsizing' => true,
                ];
            }

            $url = $this->imageUploadService->uploadImage(
                $request->file('image'),
                $directory,
                'public',
                $resizeOptions
            );

            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'Image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function uploadMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|max:10240',
            'directory' => 'nullable|string',
        ]);

        try {
            $directory = $request->input('directory', 'uploads');

            $urls = $this->imageUploadService->uploadMultipleImages(
                $request->file('images'),
                $directory,
                'public'
            );

            return response()->json([
                'success' => true,
                'urls' => $urls,
                'message' => 'Images uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        try {
            $deleted = $this->imageUploadService->deleteImage($request->input('url'));

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Image deleted successfully' : 'Image not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function generateThumbnail(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'width' => 'nullable|integer|min:50|max:500',
            'height' => 'nullable|integer|min:50|max:500',
        ]);

        try {
            $thumbnailUrl = $this->imageUploadService->generateThumbnail(
                $request->input('url'),
                $request->input('width', 300),
                $request->input('height', 300)
            );

            return response()->json([
                'success' => true,
                'thumbnail_url' => $thumbnailUrl,
                'message' => 'Thumbnail generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
