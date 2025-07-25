<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use App\Models\Place;
use Illuminate\Database\Seeder;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding images...');

        $villages = Village::all();
        $communities = Community::all();
        $smes = Sme::all();
        $places = Place::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        // Village images
        $this->command->info('Creating village images...');
        foreach ($villages as $village) {
            // Featured village images
            Image::factory()
                ->count(rand(5, 10))
                ->forVillage($village)
                ->featured()
                ->create();

            // Regular village gallery images
            Image::factory()
                ->count(rand(8, 15))
                ->forVillage($village)
                ->create();
        }

        // Place images
        if ($places->isNotEmpty()) {
            $this->command->info('Creating place images...');
            foreach ($places as $place) {
                Image::factory()
                    ->count(rand(4, 8))
                    ->forPlace($place)
                    ->create();

                // Some featured images for places
                if (rand(1, 100) <= 60) { // 60% chance
                    Image::factory()
                        ->count(rand(1, 2))
                        ->forPlace($place)
                        ->featured()
                        ->create();
                }
            }
        }

        // SME images
        if ($smes->isNotEmpty()) {
            $this->command->info('Creating SME images...');
            $randomSmes = $smes->random(min(30, $smes->count()));
            foreach ($randomSmes as $sme) {
                Image::factory()
                    ->count(rand(3, 6))
                    ->forSme($sme)
                    ->create();
            }
        }

        // Community images
        if ($communities->isNotEmpty()) {
            $this->command->info('Creating community images...');
            $randomCommunities = $communities->random(min(15, $communities->count()));
            foreach ($randomCommunities as $community) {
                Image::factory()
                    ->count(rand(4, 8))
                    ->forCommunity($community)
                    ->create();
            }
        }

        // Create some gallery sequences for better organization
        $this->command->info('Creating organized gallery sequences...');
        $randomVillages = $villages->random(min(3, $villages->count()));
        foreach ($randomVillages as $village) {
            // Create a sequence of gallery images with proper ordering
            for ($i = 1; $i <= 12; $i++) {
                Image::factory()
                    ->forVillage($village)
                    ->gallery($i)
                    ->create();
            }
        }

        $this->command->info('Images seeded successfully!');
        $this->displayImageStatistics();
    }

    private function displayImageStatistics(): void
    {
        $this->command->info("\n=== IMAGE STATISTICS ===");
        $this->command->info('Total images: ' . Image::count());
        $this->command->info('Featured images: ' . Image::where('is_featured', true)->count());

        // Scope statistics
        $this->command->info('Scope breakdown:');
        $this->command->info('  - Village images: ' . Image::whereNotNull('village_id')->whereNull('community_id')->whereNull('sme_id')->whereNull('place_id')->count());
        $this->command->info('  - Community images: ' . Image::whereNotNull('community_id')->count());
        $this->command->info('  - SME images: ' . Image::whereNotNull('sme_id')->count());
        $this->command->info('  - Place images: ' . Image::whereNotNull('place_id')->count());

        // Images with captions
        $imagesWithCaptions = Image::whereNotNull('caption')->count();
        $this->command->info("Images with captions: {$imagesWithCaptions}");

        $this->command->info('========================\n');
    }
}
