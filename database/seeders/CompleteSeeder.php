<?php

namespace Database\Seeders;

use App\Models\Village;
use App\Models\Community;
use App\Models\Place;
use App\Models\Category;
use App\Models\Sme;
use App\Models\Offer;
use App\Models\Article;
use App\Models\ExternalLink;
use App\Models\Image;
use App\Models\OfferTag;
use App\Models\OfferEcommerceLink;
use App\Models\OfferImage;
use Illuminate\Database\Seeder;

class CompleteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting complete database seeding...');

        // 1. Seed Villages first
        $this->command->info('Seeding villages...');
        $villages = $this->seedVillages();

        // 2. Seed Communities for each village
        $this->command->info('Seeding communities...');
        $communities = $this->seedCommunities($villages);

        // 3. Seed Places for each village
        $this->command->info('Seeding places...');
        $places = $this->seedPlaces($villages);

        // 4. Seed Categories for each village
        $this->command->info('Seeding categories...');
        $categories = $this->seedCategories($villages);

        // 5. Seed SMEs for each community
        $this->command->info('Seeding SMEs...');
        $smes = $this->seedSmes($communities, $places);

        // 6. Seed Offer Tags
        $this->command->info('Seeding offer tags...');
        $tags = $this->seedOfferTags();

        // 7. Seed Offers for each SME
        $this->command->info('Seeding offers...');
        $offers = $this->seedOffers($smes, $categories, $tags);

        // 8. Seed Offer Images
        $this->command->info('Seeding offer images...');
        $this->seedOfferImages($offers);

        // 9. Seed Offer E-commerce Links
        $this->command->info('Seeding offer e-commerce links...');
        $this->seedOfferEcommerceLinks($offers);

        // 10. Seed Articles
        $this->command->info('Seeding articles...');
        $this->seedArticles($villages, $communities, $smes, $places);

        // 11. Seed External Links
        $this->command->info('Seeding external links...');
        $this->seedExternalLinks($villages, $communities, $smes);

        // 12. Seed Images
        $this->command->info('Seeding images...');
        $this->seedImages($villages, $communities, $smes, $places);

        $this->command->info('Complete database seeding finished successfully!');
        $this->displaySummary();
    }

    private function seedVillages()
    {
        $specificVillages = [
            [
                'name' => 'Desa Wisata Penglipuran',
                'description' => 'Desa wisata tradisional Bali dengan arsitektur rumah adat yang masih terjaga dan kehidupan masyarakat yang harmonis dengan alam.',
                'domain' => 'penglipuran.com',
                'phone_number' => '+62 366 123456',
                'email' => 'info@penglipuran.com',
                'latitude' => -8.4385,
                'longitude' => 115.3733,
            ],
            [
                'name' => 'Desa Sade',
                'description' => 'Desa tradisional Sasak di Lombok yang mempertahankan kearifan lokal dalam arsitektur, budaya, dan kehidupan sehari-hari.',
                'phone_number' => '+62 370 987654',
                'email' => 'hello@desasade.id',
                'latitude' => -8.8913,
                'longitude' => 116.2687,
            ],
            [
                'name' => 'Desa Candirejo',
                'description' => 'Desa wisata di Magelang yang menawarkan pengalaman budaya Jawa dengan kegiatan pertanian dan kerajinan tradisional.',
                'phone_number' => '+62 293 456789',
                'email' => 'wisata@candirejo.id',
                'latitude' => -7.6053,
                'longitude' => 110.2073,
            ],
        ];

        $villages = collect();

        // Create specific villages
        foreach ($specificVillages as $villageData) {
            $villages->push(Village::factory()->active()->create($villageData));
        }

        // Create additional random villages
        $villages = $villages->merge(Village::factory()->count(3)->active()->create());

        return $villages;
    }

    private function seedCommunities($villages)
    {
        $communities = collect();

        foreach ($villages as $village) {
            $communityCount = rand(2, 4); // 2-4 communities per village
            $villageCommunities = Community::factory()
                ->count($communityCount)
                ->forVillage($village)
                ->active()
                ->create();

            $communities = $communities->merge($villageCommunities);
        }

        return $communities;
    }

    private function seedPlaces($villages)
    {
        $places = collect();

        foreach ($villages as $village) {
            $placeCount = rand(3, 6); // 3-6 places per village

            // Create different types of places
            $villagePlaces = collect();

            // Tourism places
            $villagePlaces = $villagePlaces->merge(
                Place::factory()
                    ->count(rand(1, 2))
                    ->forVillage($village)
                    ->tourism()
                    ->create()
            );

            // Historical places
            $villagePlaces = $villagePlaces->merge(
                Place::factory()
                    ->count(rand(1, 2))
                    ->forVillage($village)
                    ->historical()
                    ->create()
            );

            // Religious places
            $villagePlaces = $villagePlaces->merge(
                Place::factory()
                    ->count(rand(0, 2))
                    ->forVillage($village)
                    ->religious()
                    ->create()
            );

            $places = $places->merge($villagePlaces);
        }

        return $places;
    }

    private function seedCategories($villages)
    {
        $categories = collect();

        foreach ($villages as $village) {
            // Create product categories
            $productCategories = Category::factory()
                ->count(rand(3, 5))
                ->forVillage($village)
                ->product()
                ->create();

            // Create service categories
            $serviceCategories = Category::factory()
                ->count(rand(2, 4))
                ->forVillage($village)
                ->service()
                ->create();

            $categories = $categories->merge($productCategories)->merge($serviceCategories);
        }

        return $categories;
    }

    private function seedSmes($communities, $places)
    {
        $smes = collect();

        foreach ($communities as $community) {
            $smeCount = rand(3, 8); // 3-8 SMEs per community

            $communitySmes = collect();

            for ($i = 0; $i < $smeCount; $i++) {
                $smeFactory = Sme::factory()->forCommunity($community);

                // 40% chance to assign to a place
                if (rand(1, 100) <= 40 && $places->where('village_id', $community->village_id)->isNotEmpty()) {
                    $randomPlace = $places->where('village_id', $community->village_id)->random();
                    $smeFactory = $smeFactory->atPlace($randomPlace);
                }

                // Mix of product and service SMEs
                $smeFactory = rand(1, 2) === 1 ? $smeFactory->product() : $smeFactory->service();

                // 70% chance to be verified
                if (rand(1, 100) <= 70) {
                    $smeFactory = $smeFactory->verified();
                }

                $communitySmes->push($smeFactory->create());
            }

            $smes = $smes->merge($communitySmes);
        }

        return $smes;
    }

    private function seedOfferTags()
    {
        // Create common tags
        $commonTags = [
            'Handmade',
            'Premium',
            'Eco-Friendly',
            'Tradisional',
            'Lokal',
            'Bambu',
            'Kayu',
            'Keramik',
            'Batik',
            'Tenun',
            'Anyaman',
            'Best Seller',
            'Limited Edition',
            'Organic',
            'Tahan Lama'
        ];

        $tags = collect();

        foreach ($commonTags as $tagName) {
            $tags->push(OfferTag::factory()->create([
                'name' => $tagName,
                'usage_count' => rand(5, 50)
            ]));
        }

        // Create additional random tags
        $tags = $tags->merge(OfferTag::factory()->count(15)->create());

        return $tags;
    }

    private function seedOffers($smes, $categories, $tags)
    {
        $offers = collect();

        foreach ($smes as $sme) {
            $offerCount = rand(2, 6); // 2-6 offers per SME

            // Get categories for the same village as the SME
            $smeCategories = $categories->where('village_id', $sme->community->village_id);
            if ($smeCategories->isEmpty()) {
                continue; // Skip if no categories available
            }

            for ($i = 0; $i < $offerCount; $i++) {
                $offer = Offer::factory()
                    ->forSme($sme)
                    ->inCategory($smeCategories->random())
                    ->create();

                // Attach random tags (1-4 tags per offer)
                $randomTags = $tags->random(rand(1, 4));
                $offer->tags()->attach($randomTags->pluck('id'));

                // Update tag usage counts
                foreach ($randomTags as $tag) {
                    $tag->increment('usage_count');
                }

                $offers->push($offer);
            }
        }

        return $offers;
    }

    private function seedOfferImages($offers)
    {
        foreach ($offers as $offer) {
            $imageCount = rand(2, 6); // 2-6 images per offer

            // Create primary image
            OfferImage::factory()
                ->forOffer($offer)
                ->primary()
                ->create();

            // Create secondary images
            for ($i = 1; $i < $imageCount; $i++) {
                OfferImage::factory()
                    ->forOffer($offer)
                    ->secondary($i)
                    ->create();
            }
        }
    }

    private function seedOfferEcommerceLinks($offers)
    {
        foreach ($offers as $offer) {
            $linkCount = rand(1, 4); // 1-4 e-commerce links per offer

            $platforms = ['tokopedia', 'shopee', 'whatsapp', 'instagram'];
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
                }

                $factory->create([
                    'sort_order' => $index
                ]);
            }
        }
    }

    private function seedArticles($villages, $communities, $smes, $places)
    {
        foreach ($villages as $village) {
            // Village-level articles
            Article::factory()
                ->count(rand(3, 5))
                ->forVillage($village)
                ->published()
                ->create();

            // Some featured articles
            Article::factory()
                ->count(rand(1, 2))
                ->forVillage($village)
                ->featured()
                ->published()
                ->create();
        }

        // Community articles
        $randomCommunities = $communities->random(min(10, $communities->count()));
        foreach ($randomCommunities as $community) {
            Article::factory()
                ->count(rand(1, 3))
                ->forCommunity($community)
                ->published()
                ->create();
        }

        // SME articles
        $randomSmes = $smes->random(min(15, $smes->count()));
        foreach ($randomSmes as $sme) {
            Article::factory()
                ->count(rand(1, 2))
                ->forSme($sme)
                ->published()
                ->create();
        }

        // Place articles
        $randomPlaces = $places->random(min(8, $places->count()));
        foreach ($randomPlaces as $place) {
            Article::factory()
                ->forVillage($place->village)
                ->aboutPlace($place)
                ->published()
                ->create();
        }
    }

    private function seedExternalLinks($villages, $communities, $smes)
    {
        // Village-level external links
        foreach ($villages as $village) {
            ExternalLink::factory()
                ->count(rand(3, 6))
                ->forVillage($village)
                ->create();

            // Some social media links
            ExternalLink::factory()
                ->count(rand(2, 3))
                ->forVillage($village)
                ->socialMedia()
                ->create();
        }

        // Community external links
        $randomCommunities = $communities->random(min(8, $communities->count()));
        foreach ($randomCommunities as $community) {
            ExternalLink::factory()
                ->count(rand(2, 4))
                ->forCommunity($community)
                ->create();
        }

        // SME external links
        $randomSmes = $smes->random(min(20, $smes->count()));
        foreach ($randomSmes as $sme) {
            ExternalLink::factory()
                ->count(rand(1, 3))
                ->forSme($sme)
                ->create();

            // Add marketplace links for product SMEs
            if ($sme->type === 'product') {
                ExternalLink::factory()
                    ->forSme($sme)
                    ->marketplace()
                    ->create();
            }
        }
    }

    private function seedImages($villages, $communities, $smes, $places)
    {
        // Village images
        foreach ($villages as $village) {
            // Featured village images
            Image::factory()
                ->count(rand(5, 8))
                ->forVillage($village)
                ->featured()
                ->create();

            // Regular village images
            Image::factory()
                ->count(rand(10, 15))
                ->forVillage($village)
                ->create();
        }

        // Place images
        foreach ($places as $place) {
            Image::factory()
                ->count(rand(3, 6))
                ->forPlace($place)
                ->tourism()
                ->create();
        }

        // SME images
        $randomSmes = $smes->random(min(25, $smes->count()));
        foreach ($randomSmes as $sme) {
            Image::factory()
                ->count(rand(2, 5))
                ->forSme($sme)
                ->business()
                ->create();
        }

        // Community images
        $randomCommunities = $communities->random(min(10, $communities->count()));
        foreach ($randomCommunities as $community) {
            Image::factory()
                ->count(rand(3, 6))
                ->forCommunity($community)
                ->culture()
                ->create();
        }
    }

    private function displaySummary()
    {
        $this->command->info("\n=== SEEDING SUMMARY ===");
        $this->command->info("Villages: " . Village::count());
        $this->command->info("Communities: " . Community::count());
        $this->command->info("Places: " . Place::count());
        $this->command->info("Categories: " . Category::count());
        $this->command->info("SMEs: " . Sme::count());
        $this->command->info("Offers: " . Offer::count());
        $this->command->info("Offer Tags: " . OfferTag::count());
        $this->command->info("Offer Images: " . OfferImage::count());
        $this->command->info("Offer E-commerce Links: " . OfferEcommerceLink::count());
        $this->command->info("Articles: " . Article::count());
        $this->command->info("External Links: " . ExternalLink::count());
        $this->command->info("Images: " . Image::count());
        $this->command->info("======================\n");
    }
}
