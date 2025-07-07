<?php

namespace Database\Seeders;

use App\Models\ExternalLink;
use App\Models\Village;
use App\Models\SmeTourismPlace;
use Illuminate\Database\Seeder;

class ExternalLinkSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating external links...');

        $villages = Village::active()->get();

        if ($villages->isEmpty()) {
            $this->command->warn('No active villages found. Creating apex domain links only.');
            $this->createApexDomainLinks();
            return;
        }

        // Create village-specific links
        foreach ($villages->take(3) as $village) {
            $this->createVillageLinks($village);
        }

        // Create some apex domain links (no village)
        $this->createApexDomainLinks();

        // Create some place-specific links
        $this->createPlaceSpecificLinks();

        $this->command->info('External link seeding completed!');
    }

    private function createVillageLinks(Village $village): void
    {
        $this->command->info("Creating links for village: {$village->name}");

        $villageLinks = [
            [
                'label' => 'Village Instagram',
                'url' => 'https://instagram.com/' . strtolower(str_replace(' ', '', $village->name)),
                'icon' => 'instagram',
                'slug' => 'instagram',
            ],
            [
                'label' => 'Village WhatsApp',
                'url' => 'https://wa.me/' . str_replace(['+', ' ', '-'], '', $village->phone_number ?? '6281234567890'),
                'icon' => 'whatsapp',
                'slug' => 'whatsapp',
            ],
            [
                'label' => 'Village Info',
                'url' => $village->url,
                'icon' => 'website',
                'slug' => 'info',
            ],
            [
                'label' => 'Contact Village',
                'url' => 'mailto:' . $village->email,
                'icon' => 'email',
                'slug' => 'contact',
            ],
        ];

        foreach ($villageLinks as $index => $linkData) {
            try {
                $link = ExternalLink::create([
                    'village_id' => $village->id,
                    'label' => $linkData['label'],
                    'url' => $linkData['url'],
                    'icon' => $linkData['icon'],
                    'slug' => $linkData['slug'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);

                $this->command->info("  ✓ Created: {$linkData['label']} -> {$link->subdomain_url}");
            } catch (\Exception $e) {
                $this->command->warn("  ✗ Failed to create {$linkData['label']}: " . $e->getMessage());
            }
        }
    }

    private function createApexDomainLinks(): void
    {
        $this->command->info('Creating apex domain links...');

        $apexLinks = [
            [
                'label' => 'Main Website',
                'url' => 'https://kecamatanbayan.id',
                'icon' => 'website',
                'slug' => 'home',
            ],
            [
                'label' => 'Tourism Info',
                'url' => 'https://tourism.kecamatanbayan.id',
                'icon' => 'maps',
                'slug' => 'tourism',
            ],
            [
                'label' => 'Government Portal',
                'url' => 'https://pemda.lomboktimur.go.id',
                'icon' => 'website',
                'slug' => 'portal',
            ],
        ];

        foreach ($apexLinks as $index => $linkData) {
            try {
                $link = ExternalLink::create([
                    'village_id' => null, // Apex domain
                    'label' => $linkData['label'],
                    'url' => $linkData['url'],
                    'icon' => $linkData['icon'],
                    'slug' => $linkData['slug'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);

                $this->command->info("  ✓ Created apex link: {$linkData['label']} -> {$link->subdomain_url}");
            } catch (\Exception $e) {
                $this->command->warn("  ✗ Failed to create apex link {$linkData['label']}: " . $e->getMessage());
            }
        }
    }

    private function createPlaceSpecificLinks(): void
    {
        $this->command->info('Creating place-specific links...');

        $places = SmeTourismPlace::with('village')
            ->whereNotNull('village_id')
            ->take(5)
            ->get();

        foreach ($places as $place) {
            if (!$place->village) continue;

            try {
                $slug = \Illuminate\Support\Str::slug($place->name);

                $link = ExternalLink::create([
                    'village_id' => $place->village_id,
                    'place_id' => $place->id,
                    'label' => $place->name . ' - Maps',
                    'url' => "https://maps.google.com/search/" . urlencode($place->address ?: $place->name),
                    'icon' => 'maps',
                    'slug' => $slug,
                    'description' => "Google Maps location for {$place->name}",
                    'sort_order' => 10,
                    'is_active' => true,
                ]);

                $this->command->info("  ✓ Created place link: {$link->label} -> {$link->subdomain_url}");
            } catch (\Exception $e) {
                $this->command->warn("  ✗ Failed to create place link for {$place->name}: " . $e->getMessage());
            }
        }

        // Create some random external links for testing
        $this->command->info('Creating random test links...');

        for ($i = 0; $i < 5; $i++) {
            try {
                $village = Village::active()->inRandomOrder()->first();

                $link = ExternalLink::factory()
                    ->forVillage($village)
                    ->create();

                $this->command->info("  ✓ Created random link: {$link->label} -> {$link->subdomain_url}");
            } catch (\Exception $e) {
                $this->command->warn("  ✗ Failed to create random link: " . $e->getMessage());
            }
        }

        // Create some apex domain random links
        for ($i = 0; $i < 3; $i++) {
            try {
                $link = ExternalLink::factory()
                    ->apexDomain()
                    ->create();

                $this->command->info("  ✓ Created random apex link: {$link->label} -> {$link->subdomain_url}");
            } catch (\Exception $e) {
                $this->command->warn("  ✗ Failed to create random apex link: " . $e->getMessage());
            }
        }
    }
}
