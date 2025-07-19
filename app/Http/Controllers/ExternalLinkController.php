<?php

namespace App\Http\Controllers;

use App\Models\ExternalLink;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExternalLinkController extends Controller
{
    /**
     * Display a listing of external links
     */
    public function index(Request $request)
    {
        $query = ExternalLink::with(['village', 'community', 'sme'])
            ->where('is_active', true);

        if ($request->filled('village_id')) {
            $query->where('village_id', $request->village_id);
        }

        if ($request->filled('community_id')) {
            $query->where('community_id', $request->community_id);
        }

        if ($request->filled('sme_id')) {
            $query->where('sme_id', $request->sme_id);
        }

        $links = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($links);
    }

    /**
     * Store a new external link
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'village_id' => 'nullable|exists:villages,id',
            'community_id' => 'nullable|exists:communities,id',
            'sme_id' => 'nullable|exists:smes,id',
            'label' => 'required|string|max:255',
            'url' => 'required|url',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Generate slug
        $baseSlug = \Illuminate\Support\Str::slug($validated['label']);
        $slug = $baseSlug;
        $counter = 1;

        // Ensure slug uniqueness within the scope
        while (ExternalLink::where('slug', $slug)
            ->where('village_id', $validated['village_id'] ?? null)
            ->where('community_id', $validated['community_id'] ?? null)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        $validated['is_active'] = true;

        $link = ExternalLink::create($validated);

        return response()->json([
            'message' => 'External link created successfully',
            'link' => $link->load(['village', 'community', 'sme'])
        ], 201);
    }

    /**
     * Redirect to external link and track clicks
     */
    public function redirect(Request $request, string $slug)
    {
        Log::info('ExternalLink redirect called', [
            'slug' => $slug,
            'host' => $request->getHost(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl(),
        ]);

        $village = $request->attributes->get('village');

        if (!$village) {
            Log::warning('No village found in request attributes');
            abort(404, 'Village not found');
        }

        // Find the external link within the village scope
        $link = ExternalLink::where('slug', $slug)
            ->where('is_active', true)
            ->where(function ($query) use ($village) {
                $query->where('village_id', $village->id)
                    ->orWhereHas('community', function ($q) use ($village) {
                        $q->where('village_id', $village->id);
                    })
                    ->orWhereHas('sme.community', function ($q) use ($village) {
                        $q->where('village_id', $village->id);
                    });
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$link) {
            Log::warning('External link not found', [
                'slug' => $slug,
                'village_id' => $village->id
            ]);
            abort(404, 'Link not found');
        }

        // Increment click count
        $link->increment('click_count');

        Log::info('Redirecting to external link', [
            'link_id' => $link->id,
            'url' => $link->url,
            'click_count' => $link->click_count + 1
        ]);

        return redirect()->away($link->url);
    }

    /**
     * Get external links for a specific domain/village
     */
    public function domainLinks(Request $request)
    {
        $village = $request->attributes->get('village');

        if (!$village) {
            return response()->json(['error' => 'Village not found'], 404);
        }

        $links = Cache::remember("village_links:{$village->id}", 300, function () use ($village) {
            return ExternalLink::where('is_active', true)
                ->where(function ($query) use ($village) {
                    $query->where('village_id', $village->id)
                        ->orWhereHas('community', function ($q) use ($village) {
                            $q->where('village_id', $village->id);
                        })
                        ->orWhereHas('sme.community', function ($q) use ($village) {
                            $q->where('village_id', $village->id);
                        });
                })
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->with(['community', 'sme'])
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($link) {
                    return [
                        'id' => $link->id,
                        'label' => $link->label,
                        'slug' => $link->slug,
                        'icon' => $link->icon,
                        'description' => $link->description,
                        'click_count' => $link->click_count,
                        'scope' => $link->community
                            ? ($link->sme ? 'SME: ' . $link->sme->name : 'Community: ' . $link->community->name)
                            : 'Village',
                        'created_at' => $link->created_at,
                    ];
                });
        });

        return response()->json([
            'village' => $village->name,
            'links' => $links
        ]);
    }

    /**
     * Get statistics for a specific link
     */
    public function stats(Request $request, string $villageSlug, string $slug)
    {
        $village = Village::where('slug', $villageSlug)
            ->where('is_active', true)
            ->firstOrFail();

        $link = ExternalLink::where('slug', $slug)
            ->where('is_active', true)
            ->where(function ($query) use ($village) {
                $query->where('village_id', $village->id)
                    ->orWhereHas('community', function ($q) use ($village) {
                        $q->where('village_id', $village->id);
                    })
                    ->orWhereHas('sme.community', function ($q) use ($village) {
                        $q->where('village_id', $village->id);
                    });
            })
            ->with(['village', 'community', 'sme'])
            ->firstOrFail();

        return response()->json([
            'link' => [
                'id' => $link->id,
                'label' => $link->label,
                'slug' => $link->slug,
                'url' => $link->url,
                'click_count' => $link->click_count,
                'created_at' => $link->created_at,
                'expires_at' => $link->expires_at,
            ],
            'village' => $village->name,
            'scope' => $link->community
                ? ($link->sme ? 'SME: ' . $link->sme->name : 'Community: ' . $link->community->name)
                : 'Village'
        ]);
    }
}
