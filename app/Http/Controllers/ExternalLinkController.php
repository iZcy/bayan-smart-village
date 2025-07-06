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
            abort(404, "Link not found: {$subdomain}.{$request->getHost()}/l/{$slug}");
        }

        // Optional: Log the access for analytics
        $this->logAccess($link, $request);

        // Redirect to the actual URL
        return redirect($link->formatted_url);
    }

    private function logAccess(ExternalLink $link, Request $request): void
    {
        // You can implement click tracking here
        // For example, increment a counter or log to database

        // Simple increment (you might want to add this field to migration)
        // $link->increment('click_count');

        // Or log to Laravel log
        Log::info("External link accessed", [
            'link_id' => $link->id,
            'subdomain' => $link->subdomain,
            'slug' => $link->slug,
            'target_url' => $link->url,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now(),
        ]);
    }
}
