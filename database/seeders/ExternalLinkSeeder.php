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
        $isLocal = app()->environment('local');
        $protocol = $isLocal ? 'http' : 'https';

        if ($villages->isEmpty()) {
            $this->command->warn('No active villages found. Creating apex domain links only.');
            $this->createApexDomainLinks($protocol);
            return;
        }

        // Create village-specific links
        foreach ($villages->take(3) as $village) {
            $this->createVillageLinks($village, $protocol);
        }

        // Create some apex domain links (no village)
        $this->createApexDomainLinks($protocol);

        // Create some place-specific links
        $this->createPlaceSpecificLinks($protocol);

        $this->command->info('External link seeding completed!');

        if ($isLocal) {
            $this->command->info('🔧 Local environment detected - using HTTP protocol for links');
        } else {
            $this->command->info('🔒 Production environment - using HTTPS protocol for links');
        }
    }

    private function createVillageLinks(Village $village, string $protocol): void
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
                'url' => $village->url, // This will use the environment-aware protocol
                'icon' => 'website',
                'slug' => 'info',
            ],
            [
                'label' => 'Contact Village',
                'url' => 'mailto:' . ($village->email ?? 'contact@' . $village->slug . '.village.id'),
                'icon' => 'email',
                'slug' => 'contact',
            ],
        ];

        // Add local testing links if in local environment
        if (app()->environment('local')) {
            $villageLinks[] = [
                'label' => 'Local Test Page',
                'url' => "http://localhost:8000/test/village/{$village->slug}",
                'icon' => 'link',
                'slug' => 'test',
            ];
        }

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

    private function createApexDomainLinks(string $protocol): void
    {
        $this->command->info('Creating apex domain links...');

        $baseDomain = config('app.domain', 'kecamatanbayan.id');

        $apexLinks = [
            [
                'label' => 'Main Website',
                'url' => "{$protocol}://{$baseDomain}",
                'icon' => 'website',
                'slug' => 'home',
            ],
            [
                'label' => 'Tourism Info',
                'url' => "{$protocol}://tourism.{$baseDomain}",
                'icon' => 'maps',
                'slug' => 'tourism',
            ],
        ];

        // Add different links based on environment
        if (app()->environment('local')) {
            $apexLinks[] = [
                'label' => 'Local Admin',
                'url' => 'http://localhost:8000/admin',
                'icon' => 'website',
                'slug' => 'admin',
            ];
            $apexLinks[] = [
                'label' => 'Test API',
                'url' => 'http://localhost:8000/test/links',
                'icon' => 'link',
                'slug' => 'api-test',
            ];
        } else {
            $apexLinks[] = [
                'label' => 'Government Portal',
                'url' => 'https://pemda.lomboktimur.go.id',
                'icon' => 'website',
                'slug' => 'portal',
            ];
        }

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

    private function createPlaceSpecificLinks(string $protocol): void
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

                // Create different types of links based on environment
                if (app()->environment('local')) {
                    $targetUrl = "http://localhost:8000/test/place/{$place->id}";
                    $description = "Local test page for {$place->name}";
                } else {
                    $targetUrl = "https://maps.google.com/search/" . urlencode($place->address ?: $place->name);
                    $description = "Google Maps location for {$place->name}";
                }

                $link = ExternalLink::create([
                    'village_id' => $place->village_id,
                    'place_id' => $place->id,
                    'label' => $place->name . ' - Location',
                    'url' => $targetUrl,
                    'icon' => 'maps',
                    'slug' => $slug,
                    'description' => $description,
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

                // Generate environment-appropriate test URLs
                $testUrls = [];
                if (app()->environment('local')) {
                    $testUrls = [
                        'http://localhost:8000',
                        'http://127.0.0.1:8000',
                        'http://localhost:3000',
                        'http://httpbin.org/get',
                        'http://example.com',
                    ];
                } else {
                    $testUrls = [
                        'https://www.instagram.com/indonesia.travel',
                        'https://www.facebook.com/wonderfulindonesia',
                        'https://www.youtube.com/channel/UCvVNPfEqQr3lVKo-TQSAnRQ',
                        'https://whatsapp.com',
                        'https://maps.google.com',
                    ];
                }

                $link = ExternalLink::factory()
                    ->forVillage($village)
                    ->create([
                        'url' => fake()->randomElement($testUrls),
                    ]);

                $this->command->info("  ✓ Created random link: {$link->label} -> {$link->subdomain_url}");
            } catch (\Exception $e) {
                $this->command->warn("  ✗ Failed to create random link: " . $e->getMessage());
            }
        }

        // Create some apex domain random links
        for ($i = 0; $i < 3; $i++) {
            try {
                $testUrls = app()->environment('local')
                    ? ['http://localhost:8000', 'http://httpbin.org/json']
                    : ['https://indonesia.travel', 'https://kemenparekraf.go.id'];

                $link = ExternalLink::factory()
                    ->apexDomain()
                    ->create([
                        'url' => fake()->randomElement($testUrls),
                    ]);

                $this->command->info("  ✓ Created random apex link: {$link->label} -> {$link->subdomain_url}");
            } catch (\Exception $e) {
                $this->command->warn("  ✗ Failed to create random apex link: " . $e->getMessage());
            }
        }
    }
}
