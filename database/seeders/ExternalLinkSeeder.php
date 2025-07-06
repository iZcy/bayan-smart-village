<?php
// database/seeders/ExternalLinkSeeder.php

namespace Database\Seeders;

use App\Models\ExternalLink;
use App\Models\SmeTourismPlace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExternalLinkSeeder extends Seeder
{
    public function run(): void
    {
        // Get all places
        $places = SmeTourismPlace::all();

        if ($places->isEmpty()) {
            $this->command->warn('No places found. Skipping external links seeding.');
            return;
        }

        foreach ($places as $place) {
            // Generate subdomain from slug or create random one
            $subdomain = $this->getOrCreateSubdomain($place);

            $this->command->info("Creating links for: {$place->name} (subdomain: {$subdomain})");

            // Create standard links for each place
            $this->createStandardLinks($place, $subdomain);
        }
    }

    private function getOrCreateSubdomain(SmeTourismPlace $place): string
    {
        // If place has a slug, use it
        if (!empty($place->slug)) {
            return $place->slug;
        }

        // If place has no slug, generate one from name
        if (!empty($place->name)) {
            $slug = Str::slug($place->name);

            // Make sure it's unique by adding random suffix if needed
            $originalSlug = $slug;
            $counter = 1;

            while (ExternalLink::where('subdomain', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $this->command->info("  Generated subdomain from name: {$slug}");
            return $slug;
        }

        // Last resort: completely random slug
        $randomSlug = 'place-' . fake()->unique()->slug(2);

        // Ensure uniqueness
        while (ExternalLink::where('subdomain', $randomSlug)->exists()) {
            $randomSlug = 'place-' . fake()->unique()->slug(2);
        }

        $this->command->warn("  Using random subdomain: {$randomSlug}");
        return $randomSlug;
    }

    private function createStandardLinks(SmeTourismPlace $place, string $subdomain): void
    {
        $links = [
            [
                'label' => 'WhatsApp',
                'icon' => 'whatsapp',
                'url' => 'https://wa.me/62' . fake()->randomNumber(8, true),
                'slug' => 'contact_person',
                'sort_order' => 0,
            ],
            [
                'label' => 'Instagram',
                'icon' => 'instagram',
                'url' => 'https://instagram.com/' . fake()->userName,
                'slug' => 'instagram',
                'sort_order' => 1,
            ],
            [
                'label' => 'Website',
                'icon' => 'website',
                'url' => 'https://www.' . fake()->domainName,
                'slug' => 'website',
                'sort_order' => 2,
            ],
        ];

        // Add category-specific links
        if ($place->category && $place->category->type === 'sme') {
            $links[] = [
                'label' => 'Tokopedia',
                'icon' => 'tokopedia',
                'url' => 'https://tokopedia.com/' . fake()->userName,
                'slug' => 'tokopedia',
                'sort_order' => 3,
            ];
            $links[] = [
                'label' => 'Menu Online',
                'icon' => 'link',
                'url' => 'https://example.com/menu/' . fake()->slug,
                'slug' => 'menu',
                'sort_order' => 4,
            ];
        } else {
            $links[] = [
                'label' => 'Google Maps',
                'icon' => 'maps',
                'url' => 'https://maps.google.com/search/' . urlencode($place->address ?? 'Lombok'),
                'slug' => 'lokasi',
                'sort_order' => 3,
            ];
        }

        // Create the links directly
        foreach ($links as $linkData) {
            try {
                // Check for duplicate subdomain+slug combination
                $existing = ExternalLink::where('subdomain', $subdomain)
                    ->where('slug', $linkData['slug'])
                    ->first();

                if ($existing) {
                    $this->command->warn("  ⚠ Skipping {$linkData['label']} - combination already exists");
                    continue;
                }

                ExternalLink::create([
                    'place_id' => $place->id,
                    'label' => $linkData['label'],
                    'url' => $linkData['url'],
                    'icon' => $linkData['icon'],
                    'subdomain' => $subdomain,
                    'slug' => $linkData['slug'],
                    'sort_order' => $linkData['sort_order'],
                ]);

                $this->command->info("  ✓ Created {$linkData['label']} link: {$subdomain}.kecamatanbayan.id/l/{$linkData['slug']}");
            } catch (\Exception $e) {
                $this->command->error("  ✗ Failed to create {$linkData['label']} link: " . $e->getMessage());
            }
        }
    }
}
