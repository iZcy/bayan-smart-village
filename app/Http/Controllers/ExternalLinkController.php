<?php

namespace App\Http\Controllers;

use App\Models\ExternalLink;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalLinkController extends Controller
{
    public function redirect(Request $request, string $slug)
    {
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        // First, try to find by village subdomain
        if ($subdomain) {
            $village = Village::bySlug($subdomain)->active()->first();

            if ($village) {
                // Look for link in this village
                $link = ExternalLink::where('village_id', $village->id)
                    ->where('slug', $slug)
                    ->active()
                    ->first();

                if ($link) {
                    return $this->processLinkRedirect($link, $request);
                }
            }
        }

        // Fallback: Look for apex domain links (no village)
        $link = ExternalLink::whereNull('village_id')
            ->where('slug', $slug)
            ->active()
            ->first();

        if ($link) {
            return $this->processLinkRedirect($link, $request);
        }

        abort(404, "Short link not found: {$host}/l/{$slug}");
    }

    private function extractSubdomain(string $host): ?string
    {
        $baseDomain = config('app.domain', 'kecamatanbayan.id');

        // Remove the base domain to get subdomain
        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);
            return $subdomain !== $host ? $subdomain : null;
        }

        return null;
    }

    private function processLinkRedirect(ExternalLink $link, Request $request)
    {
        // Log the access and increment click count
        $this->logAccess($link, $request);

        // Redirect to the actual URL
        return redirect($link->formatted_url);
    }

    private function logAccess(ExternalLink $link, Request $request): void
    {
        // Increment click count
        $link->increment('click_count');

        // Log the access for analytics
        Log::info("Short link accessed", [
            'link_id' => $link->id,
            'label' => $link->label,
            'village_id' => $link->village_id,
            'village_name' => $link->village?->name,
            'slug' => $link->slug,
            'target_url' => $link->url,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'referer' => $request->header('referer'),
            'timestamp' => now(),
        ]);
    }

    /**
     * API endpoint to create a short link
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'label' => 'required|string|max:255',
            'village_id' => 'sometimes|uuid|exists:villages,id',
            'place_id' => 'sometimes|uuid|exists:sme_tourism_places,id',
            'slug' => 'sometimes|string|regex:/^[a-z0-9_-]+$/',
            'description' => 'sometimes|string',
            'icon' => 'sometimes|string',
            'expires_at' => 'sometimes|date|after:now',
        ]);

        // Generate slug if not provided
        $slug = $validated['slug'] ?? ExternalLink::generateRandomSlug();

        // Check for unique slug within the domain
        $exists = ExternalLink::where('village_id', $validated['village_id'] ?? null)
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'This slug already exists for the specified domain'
            ], 422);
        }

        // Create the link
        $link = ExternalLink::create([
            'village_id' => $validated['village_id'] ?? null,
            'place_id' => $validated['place_id'] ?? null,
            'label' => $validated['label'],
            'url' => $validated['url'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'link',
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $link->id,
                'label' => $link->label,
                'short_url' => $link->subdomain_url,
                'target_url' => $link->formatted_url,
                'village_id' => $link->village_id,
                'village_name' => $link->village?->name,
                'slug' => $link->slug,
                'is_active' => $link->is_active,
                'expires_at' => $link->expires_at,
                'created_at' => $link->created_at,
            ]
        ], 201);
    }

    /**
     * API endpoint to get link statistics
     */
    public function stats(string $subdomain, string $slug)
    {
        // Try to find by village first
        $village = Village::bySlug($subdomain)->first();

        if ($village) {
            $link = ExternalLink::where('village_id', $village->id)
                ->where('slug', $slug)
                ->first();
        } else {
            // Fallback to apex domain lookup
            $link = ExternalLink::whereNull('village_id')
                ->where('slug', $slug)
                ->first();
        }

        if (!$link) {
            return response()->json(['error' => 'Link not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $link->id,
                'label' => $link->label,
                'short_url' => $link->subdomain_url,
                'target_url' => $link->formatted_url,
                'village_id' => $link->village_id,
                'village_name' => $link->village?->name,
                'click_count' => $link->click_count,
                'is_active' => $link->is_active,
                'expires_at' => $link->expires_at,
                'created_at' => $link->created_at,
                'updated_at' => $link->updated_at,
            ]
        ]);
    }

    /**
     * API endpoint to list all links (with pagination)
     */
    public function index(Request $request)
    {
        $query = ExternalLink::with(['village', 'place']);

        // Filter by village
        if ($request->has('village_id')) {
            $query->where('village_id', $request->string('village_id'));
        }

        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by expiration
        if ($request->has('expired')) {
            if ($request->boolean('expired')) {
                $query->whereNotNull('expires_at')->where('expires_at', '<', now());
            } else {
                $query->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $links = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $links->items(),
            'pagination' => [
                'current_page' => $links->currentPage(),
                'last_page' => $links->lastPage(),
                'per_page' => $links->perPage(),
                'total' => $links->total(),
            ]
        ]);
    }
}
