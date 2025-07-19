<?php

namespace Database\Seeders;

use App\Models\ExternalLink;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use Illuminate\Database\Seeder;

class ExternalLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding external links...');

        $villages = Village::all();
        $communities = Community::all();
        $smes = Sme::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        // Village-level external links
        $this->command->info('Creating village external links...');
        foreach ($villages as $village) {
            // Social media links for villages
            ExternalLink::factory()
                ->count(rand(2, 4))
                ->forVillage($village)
                ->socialMedia()
                ->create();

            // General village links
            ExternalLink::factory()
                ->count(rand(2, 3))
                ->forVillage($village)
                ->create();

            // Some popular links
            ExternalLink::factory()
                ->count(rand(1, 2))
                ->forVillage($village)
                ->popular()
                ->create();
        }

        // Community external links
        if ($communities->isNotEmpty()) {
            $this->command->info('Creating community external links...');
            $randomCommunities = $communities->random(min(12, $communities->count()));
            foreach ($randomCommunities as $community) {
                ExternalLink::factory()
                    ->count(rand(2, 4))
                    ->forCommunity($community)
                    ->create();

                // Social media for communities
                ExternalLink::factory()
                    ->forCommunity($community)
                    ->socialMedia()
                    ->create();
            }
        }

        // SME external links
        if ($smes->isNotEmpty()) {
            $this->command->info('Creating SME external links...');
            $randomSmes = $smes->random(min(25, $smes->count()));
            foreach ($randomSmes as $sme) {
                // Basic links for SMEs
                ExternalLink::factory()
                    ->count(rand(1, 3))
                    ->forSme($sme)
                    ->create();

                // Marketplace links for product SMEs
                if ($sme->type === 'product') {
                    ExternalLink::factory()
                        ->count(rand(1, 2))
                        ->forSme($sme)
                        ->marketplace()
                        ->create();
                }

                // Social media for SMEs
                if (rand(1, 100) <= 70) { // 70% chance
                    ExternalLink::factory()
                        ->forSme($sme)
                        ->socialMedia()
                        ->create();
                }
            }
        }

        // Create some temporary/promotional links
        $this->command->info('Creating temporary promotional links...');
        ExternalLink::factory()
            ->count(5)
            ->forVillage($villages->random())
            ->temporary()
            ->create();

        $this->command->info('External links seeded successfully!');
        $this->displayExternalLinkStatistics();
    }

    private function displayExternalLinkStatistics(): void
    {
        $this->command->info("\n=== EXTERNAL LINK STATISTICS ===");
        $this->command->info('Total external links: ' . ExternalLink::count());
        $this->command->info('Active links: ' . ExternalLink::where('is_active', true)->count());
        $this->command->info('Links with expiration: ' . ExternalLink::whereNotNull('expires_at')->count());

        // Scope statistics
        $this->command->info('Scope breakdown:');
        $this->command->info('  - Village links: ' . ExternalLink::whereNotNull('village_id')->count());
        $this->command->info('  - Community links: ' . ExternalLink::whereNotNull('community_id')->count());
        $this->command->info('  - SME links: ' . ExternalLink::whereNotNull('sme_id')->count());

        // Popular links
        $popularLinks = ExternalLink::where('click_count', '>', 50)->count();
        $this->command->info("Popular links (>50 clicks): {$popularLinks}");

        $this->command->info('=================================\n');
    }
}
