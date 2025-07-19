<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Sme;
use App\Models\Category;
use App\Models\OfferTag;
use App\Models\OfferImage;
use App\Models\OfferEcommerceLink;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding offers...');

        $smes = Sme::with('community.village')->get();
        $categories = Category::all();
        $tags = OfferTag::all();

        if ($smes->isEmpty()) {
            $this->command->warn('No SMEs found. Please run SmeSeeder first.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        foreach ($smes as $sme) {
            $offerCount = rand(2, 6); // 2-6 offers per SME

            // Get categories for the same village as the SME
            $smeCategories = $categories->where('village_id', $sme->community->village_id);

            if ($smeCategories->isEmpty()) {
                $this->command->warn("No categories found for village {$sme->community->village->name}. Skipping SME {$sme->name}");
                continue;
            }

            $this->command->info("Creating {$offerCount} offers for {$sme->name}");

            for ($i = 0; $i < $offerCount; $i++) {
                // Create the offer
                $offer = Offer::factory()
                    ->forSme($sme)
                    ->inCategory($smeCategories->random())
                    ->create();

                // Attach random tags (1-4 tags per offer)
                if ($tags->isNotEmpty()) {
                    $randomTags = $tags->random(rand(1, min(4, $tags->count())));
                    $offer->tags()->attach($randomTags->pluck('id'));

                    // Update tag usage counts
                    foreach ($randomTags as $tag) {
                        $tag->increment('usage_count');
                    }
                }

                // Create offer images
                $this->createOfferImages($offer);

                // Create e-commerce links
                $this->createOfferEcommerceLinks($offer);
            }
        }

        $this->command->info('Offers seeded successfully!');
        $this->displayOfferStatistics();
    }

    private function createOfferImages(Offer $offer): void
    {
        $imageCount = rand(2, 5); // 2-5 images per offer

        // Create primary image
        OfferImage::factory()
            ->forOffer($offer)
            ->primary()
            ->create();

        // Create secondary images
        for ($i = 1; $i < $imageCount; $i++) {
            $factory = OfferImage::factory()
                ->forOffer($offer)
                ->secondary($i);

            // Vary image types
            switch (rand(1, 4)) {
                case 1:
                    $factory = $factory->detail();
                    break;
                case 2:
                    $factory = $factory->process();
                    break;
                case 3:
                    $factory = $factory->lifestyle();
                    break;
                default:
                    // Keep as regular secondary
                    break;
            }

            $factory->create();
        }
    }

    private function createOfferEcommerceLinks(Offer $offer): void
    {
        $linkCount = rand(1, 4); // 1-4 e-commerce links per offer

        $platforms = ['tokopedia', 'shopee', 'whatsapp', 'instagram', 'website'];
        $selectedPlatforms = array_slice($platforms, 0, $linkCount);

        foreach ($selectedPlatforms as $index => $platform) {
            $factory = OfferEcommerceLink::factory()
                ->forOffer($offer);

            switch ($platform) {
                case 'tokopedia':
                    $factory = $factory->tokopedia();
                    break;
                case 'shopee':
                    $factory = $factory->shopee();
                    break;
                case 'whatsapp':
                    $factory = $factory->whatsapp();
                    break;
                case 'instagram':
                    $factory = $factory->instagram();
                    break;
                case 'website':
                    $factory = $factory->website();
                    break;
            }

            // 70% chance to be verified
            if (rand(1, 100) <= 70) {
                $factory = $factory->verified();
            }

            $factory->create([
                'sort_order' => $index
            ]);
        }
    }

    private function displayOfferStatistics(): void
    {
        $this->command->info("\n=== OFFER STATISTICS ===");
        $this->command->info('Total offers: ' . Offer::count());
        $this->command->info('Featured offers: ' . Offer::where('is_featured', true)->count());
        $this->command->info('Active offers: ' . Offer::where('is_active', true)->count());

        // Availability statistics
        $availabilityStats = Offer::selectRaw('availability, COUNT(*) as count')
            ->groupBy('availability')
            ->pluck('count', 'availability');

        $this->command->info('Availability breakdown:');
        foreach ($availabilityStats as $availability => $count) {
            $this->command->info("  - {$availability}: {$count}");
        }

        $this->command->info('Total offer images: ' . OfferImage::count());
        $this->command->info('Total e-commerce links: ' . OfferEcommerceLink::count());
        $this->command->info('========================\n');
    }
}
