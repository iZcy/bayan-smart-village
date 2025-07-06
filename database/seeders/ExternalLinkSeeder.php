<?php
// database/seeders/ExternalLinkSeeder.php

namespace Database\Seeders;

use App\Models\ExternalLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExternalLinkSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating sample shortened links...');

        $sampleLinks = [
            [
                'label' => 'Company Instagram',
                'url' => 'https://instagram.com/kecamatanbayan',
                'icon' => 'instagram',
                'subdomain' => 'official',
                'slug' => 'instagram',
                'description' => 'Official Instagram account',
            ],
            [
                'label' => 'Contact WhatsApp',
                'url' => 'https://wa.me/6281234567890',
                'icon' => 'whatsapp',
                'subdomain' => 'contact',
                'slug' => 'whatsapp',
                'description' => 'Contact us via WhatsApp',
            ],
            [
                'label' => 'Main Website',
                'url' => 'https://kecamatanbayan.id',
                'icon' => 'website',
                'subdomain' => 'www',
                'slug' => 'home',
                'description' => 'Main website homepage',
            ],
            [
                'label' => 'YouTube Channel',
                'url' => 'https://youtube.com/@kecamatanbayan',
                'icon' => 'youtube',
                'subdomain' => 'media',
                'slug' => 'youtube',
                'description' => 'Official YouTube channel',
            ],
            [
                'label' => 'Facebook Page',
                'url' => 'https://facebook.com/kecamatanbayan',
                'icon' => 'facebook',
                'subdomain' => 'social',
                'slug' => 'facebook',
                'description' => 'Official Facebook page',
            ],
        ];

        foreach ($sampleLinks as $index => $linkData) {
            try {
                // Check for existing combination
                $existing = ExternalLink::where('subdomain', $linkData['subdomain'])
                    ->where('slug', $linkData['slug'])
                    ->first();

                if ($existing) {
                    $this->command->warn("  ⚠ Skipping {$linkData['label']} - combination already exists");
                    continue;
                }

                $link = ExternalLink::create([
                    'label' => $linkData['label'],
                    'url' => $linkData['url'],
                    'icon' => $linkData['icon'],
                    'subdomain' => $linkData['subdomain'],
                    'slug' => $linkData['slug'],
                    'description' => $linkData['description'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);

                $shortUrl = $link->subdomain_url;
                $this->command->info("  ✓ Created {$linkData['label']}: {$shortUrl}");
            } catch (\Exception $e) {
                $this->command->error("  ✗ Failed to create {$linkData['label']}: " . $e->getMessage());
            }
        }

        // Create some random links
        $this->command->info('Creating random links...');

        for ($i = 0; $i < 10; $i++) {
            try {
                $subdomain = ExternalLink::generateRandomSubdomain();
                $slug = ExternalLink::generateRandomSlug();

                $link = ExternalLink::factory()->create([
                    'subdomain' => $subdomain,
                    'slug' => $slug,
                ]);

                $this->command->info("  ✓ Created random link: {$link->subdomain_url}");
            } catch (\Exception $e) {
                $this->command->warn("  ⚠ Failed to create random link: " . $e->getMessage());
            }
        }

        $this->command->info('External link seeding completed!');
    }
}
