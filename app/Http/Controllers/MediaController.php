<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MediaController extends Controller
{
    /**
     * Get media for a specific context and village
     */
    public function getContextMedia(Request $request, string $context)
    {
        $village = $request->attributes->get('village');
        $type = $request->get('type'); // 'video' or 'audio'

        $cacheKey = "media:{$village?->id}:{$context}:{$type}";

        $media = Cache::remember($cacheKey, 300, function () use ($village, $context, $type) {
            return Media::getForContext($context, $village?->id, $type);
        });

        return response()->json([
            'context' => $context,
            'village' => $village?->name,
            'media' => $media->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'type' => $item->type,
                    'file_url' => $item->file_url,
                    'thumbnail_url' => $item->thumbnail_url,
                    'duration' => $item->duration,
                    'formatted_duration' => $item->formatted_duration,
                    'is_featured' => $item->is_featured,
                    'autoplay' => $item->autoplay,
                    'loop' => $item->loop,
                    'muted' => $item->muted,
                    'volume' => $item->volume,
                    'settings' => $item->settings,
                ];
            })
        ]);
    }

    /**
     * Get featured media for a specific context
     */
    public function getFeaturedMedia(Request $request, string $context)
    {
        $village = $request->attributes->get('village');
        $type = $request->get('type'); // 'video' or 'audio'

        $cacheKey = "featured_media:{$village?->id}:{$context}:{$type}";

        $media = Cache::remember($cacheKey, 300, function () use ($village, $context, $type) {
            return Media::getFeaturedForContext($context, $village?->id, $type);
        });

        if (!$media) {
            return response()->json([
                'context' => $context,
                'village' => $village?->name,
                'media' => null,
                'message' => 'No featured media found for this context'
            ]);
        }

        return response()->json([
            'context' => $context,
            'village' => $village?->name,
            'media' => [
                'id' => $media->id,
                'title' => $media->title,
                'description' => $media->description,
                'type' => $media->type,
                'file_url' => $media->file_url,
                'thumbnail_url' => $media->thumbnail_url,
                'duration' => $media->duration,
                'formatted_duration' => $media->formatted_duration,
                'is_featured' => $media->is_featured,
                'autoplay' => $media->autoplay,
                'loop' => $media->loop,
                'muted' => $media->muted,
                'volume' => $media->volume,
                'settings' => $media->settings,
            ]
        ]);
    }

    /**
     * Get all media for village admin
     */
    public function index(Request $request)
    {
        $village = $request->attributes->get('village');

        $query = Media::query();

        if ($village) {
            $query->where('village_id', $village->id);
        }

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('context')) {
            $query->where('context', $request->context);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $media = $query->with(['village', 'community', 'sme', 'place'])
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($media);
    }

    /**
     * Store new media
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,audio',
            'context' => 'required|in:home,places,products,articles,gallery,global',
            'file_url' => 'required|url',
            'thumbnail_url' => 'nullable|url',
            'duration' => 'nullable|integer|min:0',
            'mime_type' => 'nullable|string',
            'file_size' => 'nullable|integer|min:0',
            'village_id' => 'nullable|exists:villages,id',
            'community_id' => 'nullable|exists:communities,id',
            'sme_id' => 'nullable|exists:smes,id',
            'place_id' => 'nullable|exists:places,id',
            'is_featured' => 'boolean',
            'autoplay' => 'boolean',
            'loop' => 'boolean',
            'muted' => 'boolean',
            'volume' => 'numeric|min:0|max:1',
            'sort_order' => 'integer|min:0',
        ]);

        $media = Media::create($validated);

        // Clear relevant cache
        $this->clearMediaCache($media);

        return response()->json([
            'message' => 'Media created successfully',
            'media' => $media->load(['village', 'community', 'sme', 'place'])
        ], 201);
    }

    /**
     * Update media
     */
    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,audio',
            'context' => 'required|in:home,places,products,articles,gallery,global',
            'file_url' => 'required|url',
            'thumbnail_url' => 'nullable|url',
            'duration' => 'nullable|integer|min:0',
            'mime_type' => 'nullable|string',
            'file_size' => 'nullable|integer|min:0',
            'village_id' => 'nullable|exists:villages,id',
            'community_id' => 'nullable|exists:communities,id',
            'sme_id' => 'nullable|exists:smes,id',
            'place_id' => 'nullable|exists:places,id',
            'is_featured' => 'boolean',
            'autoplay' => 'boolean',
            'loop' => 'boolean',
            'muted' => 'boolean',
            'volume' => 'numeric|min:0|max:1',
            'sort_order' => 'integer|min:0',
        ]);

        // Clear cache before update
        $this->clearMediaCache($media);

        $media->update($validated);

        // Clear cache after update
        $this->clearMediaCache($media);

        return response()->json([
            'message' => 'Media updated successfully',
            'media' => $media->load(['village', 'community', 'sme', 'place'])
        ]);
    }

    /**
     * Delete media
     */
    public function destroy(Media $media)
    {
        $this->clearMediaCache($media);

        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully'
        ]);
    }

    /**
     * Clear media cache
     */
    private function clearMediaCache(Media $media)
    {
        $patterns = [
            "media:{$media->village_id}:{$media->context}:video",
            "media:{$media->village_id}:{$media->context}:audio",
            "media:{$media->village_id}:{$media->context}:",
            "featured_media:{$media->village_id}:{$media->context}:video",
            "featured_media:{$media->village_id}:{$media->context}:audio",
            "featured_media:{$media->village_id}:{$media->context}:",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Get media statistics
     */
    public function stats(Request $request)
    {
        $village = $request->attributes->get('village');

        $query = Media::query();
        if ($village) {
            $query->where('village_id', $village->id);
        }

        $stats = [
            'total' => $query->count(),
            'videos' => $query->where('type', 'video')->count(),
            'audios' => $query->where('type', 'audio')->count(),
            'featured' => $query->where('is_featured', true)->count(),
            'active' => $query->where('is_active', true)->count(),
            'by_context' => $query->selectRaw('context, COUNT(*) as count')
                ->groupBy('context')
                ->pluck('count', 'context'),
            'autoplay_enabled' => $query->where('autoplay', true)->count(),
        ];

        return response()->json([
            'village' => $village?->name,
            'statistics' => $stats
        ]);
    }
}
