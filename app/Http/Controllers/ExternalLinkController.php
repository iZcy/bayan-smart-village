<?php
// app/Http/Controllers/ExternalLinkController.php

namespace App\Http\Controllers;

use App\Models\ExternalLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalLinkController extends Controller
{
    public function redirect(Request $request, string $subdomain, string $slug)
    {
        // Find the external link by subdomain and slug
        $link = ExternalLink::where('subdomain', $subdomain)
            ->where('slug', $slug)
            ->first();

        if (!$link) {
            abort(404, "Short link not found: {$subdomain}.{$request->getHost()}/l/{$slug}");
        }

        // Check if link is active
        if (!$link->is_active) {
            abort(410, "This short link has been deactivated");
        }

        // Check if link has expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(410, "This short link has expired");
        }

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
            'subdomain' => $link->subdomain,
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
            'subdomain' => 'sometimes|string|regex:/^[a-z0-9-]+$/',
            'slug' => 'sometimes|string|regex:/^[a-z0-9_-]+$/',
            'description' => 'sometimes|string',
            'icon' => 'sometimes|string',
            'expires_at' => 'sometimes|date|after:now',
        ]);

        // Generate subdomain and slug if not provided
        $subdomain = $validated['subdomain'] ?? ExternalLink::generateRandomSubdomain();
        $slug = $validated['slug'] ?? ExternalLink::generateRandomSlug();

        // Check for unique combination
        if (ExternalLink::where('subdomain', $subdomain)->where('slug', $slug)->exists()) {
            return response()->json([
                'error' => 'This subdomain and slug combination already exists'
            ], 422);
        }

        // Create the link
        $link = ExternalLink::create([
            'label' => $validated['label'],
            'url' => $validated['url'],
            'subdomain' => $subdomain,
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
                'subdomain' => $link->subdomain,
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
        $link = ExternalLink::where('subdomain', $subdomain)
            ->where('slug', $slug)
            ->first();

        if (!$link) {
            return response()->json(['error' => 'Link not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $link->id,
                'label' => $link->label,
                'short_url' => $link->subdomain_url,
                'target_url' => $link->formatted_url,
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
        $query = ExternalLink::query();

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
                    ->orWhere('subdomain', 'like', "%{$search}%")
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
