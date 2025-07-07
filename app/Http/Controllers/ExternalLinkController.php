<?php

namespace App\Http\Controllers;

use App\Models\ExternalLink;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;

class ExternalLinkController extends Controller
{
    public function redirect(Request $request)
    {
        // Extract the actual slug from the path instead of using the route parameter
        $pathSegments = explode('/', trim($request->path(), '/'));
        $actualSlug = end($pathSegments); // Gets "warung-bu-sari" from "l/warung-bu-sari"

        Log::info('ExternalLinkController::redirect called', [
            'actual_slug' => $actualSlug, // This will be "warung-bu-sari" (correct)
            'host' => $request->getHost(),
            'path' => $request->path(),
            'url' => $request->url(),
        ]);

        // Use the actual slug instead of the route parameter
        $slug = $actualSlug;

        // Get village from middleware (if on subdomain/custom domain)
        $village = $request->attributes->get('village');

        Log::info('Village resolution in controller', [
            'village_found' => $village ? $village->name : 'none',
            'village_id' => $village ? $village->id : 'none',
        ]);

        // Look for link based on village context
        if ($village) {
            $slug = basename(parse_url($request->url(), PHP_URL_PATH));
            // Look for link in this specific village
            $link = ExternalLink::where('village_id', $village->id)
                ->where('slug', $slug)
                ->active()
                ->first();

            Log::info('Village link search', [
                'village_id' => $village->id,
                'slug' => $slug,
                'link_found' => $link ? $link->id : 'none',
            ]);

            if ($link) {
                return $this->processLinkRedirect($link, $request);
            }
        } else {
            // Look for apex domain links (no village)
            $link = ExternalLink::whereNull('village_id')
                ->where('slug', $slug)
                ->active()
                ->first();

            Log::info('Apex domain link search', [
                'slug' => $slug,
                'link_found' => $link ? $link->id : 'none',
            ]);

            if ($link) {
                return $this->processLinkRedirect($link, $request);
            }
        }

        Log::error('Link not found', [
            'slug' => $slug,
            'village' => $village ? $village->name : 'none',
            'host' => $request->getHost(),
        ]);

        // Create helpful error message
        $domain = $village ? $village->full_domain : $request->getHost();
        abort(404, "Short link not found: {$domain}/l/{$slug}");
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
            'village_domain' => $link->village?->full_domain,
            'slug' => $link->slug,
            'target_url' => $link->url,
            'accessing_domain' => $request->getHost(),
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
            'village_slug' => 'sometimes|string|exists:villages,slug',
            'village_id' => 'sometimes|uuid|exists:villages,id',
            'place_id' => 'sometimes|uuid|exists:sme_tourism_places,id',
            'slug' => 'sometimes|string|regex:/^[a-z0-9_-]+$/',
            'description' => 'sometimes|string',
            'icon' => 'sometimes|string',
            'expires_at' => 'sometimes|date|after:now',
        ]);

        // Resolve village_id from village_slug if provided
        if (isset($validated['village_slug']) && !isset($validated['village_id'])) {
            $village = Village::where('slug', $validated['village_slug'])->first();
            $validated['village_id'] = $village?->id;
        }

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
                'village_domain' => $link->village?->full_domain,
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
    public function stats(string $villageSlug, string $slug)
    {
        // Find village by slug
        $village = Village::where('slug', $villageSlug)->first();

        if ($village) {
            $link = ExternalLink::where('village_id', $village->id)
                ->where('slug', $slug)
                ->first();
        } else {
            // Fallback to apex domain lookup if no village found
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
                'village_domain' => $link->village?->full_domain,
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

        // Filter by village slug or ID
        if ($request->has('village_slug')) {
            $village = Village::where('slug', $request->string('village_slug'))->first();
            if ($village) {
                $query->where('village_id', $village->id);
            }
        } elseif ($request->has('village_id')) {
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

    /**
     * Get all links for the current domain/village
     */
    public function domainLinks(Request $request)
    {
        // Get village from middleware instead of re-resolving
        $village = $request->attributes->get('village');

        $query = ExternalLink::active()->orderBy('sort_order');

        if ($village) {
            $query->where('village_id', $village->id);
        } else {
            $query->whereNull('village_id');
        }

        $links = $query->get();

        return response()->json([
            'domain' => $request->getHost(),
            'village' => $village ? $village->name : 'Main Site',
            'links' => $links->map(function ($link) {
                return [
                    'label' => $link->label,
                    'short_url' => $link->subdomain_url,
                    'icon' => $link->icon,
                    'description' => $link->description,
                    'click_count' => $link->click_count,
                ];
            })
        ]);
    }
}
