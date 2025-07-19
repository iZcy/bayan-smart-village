<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use App\Models\Place;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding articles...');

        $villages = Village::all();
        $communities = Community::all();
        $smes = Sme::all();
        $places = Place::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        // Village-level articles
        $this->command->info('Creating village articles...');
        foreach ($villages as $village) {
            // Regular village articles
            Article::factory()
                ->count(rand(3, 6))
                ->forVillage($village)
                ->published()
                ->create();

            // Featured village articles
            Article::factory()
                ->count(rand(1, 2))
                ->forVillage($village)
                ->featured()
                ->published()
                ->tourism()
                ->create();

            // Some draft articles
            Article::factory()
                ->count(rand(0, 2))
                ->forVillage($village)
                ->draft()
                ->create();
        }

        // Community articles
        if ($communities->isNotEmpty()) {
            $this->command->info('Creating community articles...');
            $randomCommunities = $communities->random(min(10, $communities->count()));
            foreach ($randomCommunities as $community) {
                Article::factory()
                    ->count(rand(1, 3))
                    ->forCommunity($community)
                    ->published()
                    ->culture()
                    ->create();
            }
        }

        // SME articles (business stories)
        if ($smes->isNotEmpty()) {
            $this->command->info('Creating SME articles...');
            $randomSmes = $smes->random(min(15, $smes->count()));
            foreach ($randomSmes as $sme) {
                Article::factory()
                    ->count(rand(1, 2))
                    ->forSme($sme)
                    ->published()
                    ->business()
                    ->create();
            }
        }

        // Place articles (destination features)
        if ($places->isNotEmpty()) {
            $this->command->info('Creating place articles...');
            $randomPlaces = $places->random(min(12, $places->count()));
            foreach ($randomPlaces as $place) {
                Article::factory()
                    ->forVillage($place->village)
                    ->aboutPlace($place)
                    ->published()
                    ->tourism()
                    ->create();
            }
        }

        $this->command->info('Articles seeded successfully!');
        $this->displayArticleStatistics();
    }

    private function displayArticleStatistics(): void
    {
        $this->command->info("\n=== ARTICLE STATISTICS ===");
        $this->command->info('Total articles: ' . Article::count());
        $this->command->info('Published articles: ' . Article::where('is_published', true)->count());
        $this->command->info('Featured articles: ' . Article::where('is_featured', true)->count());
        $this->command->info('Draft articles: ' . Article::where('is_published', false)->count());

        // Scope statistics
        $this->command->info('Scope breakdown:');
        $this->command->info('  - Village articles: ' . Article::whereNotNull('village_id')->whereNull('community_id')->whereNull('sme_id')->count());
        $this->command->info('  - Community articles: ' . Article::whereNotNull('community_id')->whereNull('sme_id')->count());
        $this->command->info('  - SME articles: ' . Article::whereNotNull('sme_id')->count());
        $this->command->info('  - Place articles: ' . Article::whereNotNull('place_id')->count());
        $this->command->info('===========================\n');
    }
}
