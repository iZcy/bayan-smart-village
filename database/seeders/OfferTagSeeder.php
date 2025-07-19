<?php

namespace Database\Seeders;

use App\Models\OfferTag;
use Illuminate\Database\Seeder;

class OfferTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding offer tags...');

        // Create common predefined tags
        $commonTags = [
            // Material tags
            'Bambu' => 15,
            'Kayu' => 12,
            'Rotan' => 8,
            'Pandan' => 6,
            'Keramik' => 10,
            'Kain' => 14,
            'Kulit' => 7,

            // Quality tags
            'Handmade' => 25,
            'Premium' => 18,
            'Limited Edition' => 5,
            'Eco-Friendly' => 20,
            'Organic' => 15,
            'Berkualitas Tinggi' => 12,

            // Origin tags
            'Tradisional' => 22,
            'Lokal' => 30,
            'Asli' => 16,
            'Warisan' => 8,
            'Budaya' => 14,

            // Product type tags
            'Kerajinan' => 28,
            'Makanan' => 20,
            'Minuman' => 15,
            'Tekstil' => 12,
            'Aksesoris' => 10,

            // Feature tags
            'Tahan Lama' => 18,
            'Ringan' => 10,
            'Unik' => 22,
            'Mudah Dibersihkan' => 8,
            'Anti Air' => 6,

            // Style tags
            'Modern' => 15,
            'Klasik' => 12,
            'Minimalis' => 10,
            'Etnik' => 18,
            'Kontemporer' => 8,

            // Occasion tags
            'Souvenir' => 25,
            'Hadiah' => 20,
            'Koleksi' => 12,
            'Dekorasi' => 16,
            'Fungsional' => 14,

            // Certification tags
            'Halal' => 15,
            'BPOM' => 8,
            'SNI' => 6,
            'ISO' => 4,
            'Fair Trade' => 5,

            // Popular tags
            'Best Seller' => 30,
            'Trending' => 25,
            'Populer' => 20,
            'Rekomendasi' => 18,
            'Favorit' => 22,
        ];

        foreach ($commonTags as $tagName => $usageCount) {
            OfferTag::factory()->create([
                'name' => $tagName,
                'usage_count' => $usageCount,
            ]);
        }

        // Create additional random tags
        OfferTag::factory()
            ->count(10)
            ->create();

        $this->command->info('Offer tags seeded successfully!');
        $this->command->info('Total tags created: ' . OfferTag::count());

        // Show most popular tags
        $popularTags = OfferTag::orderBy('usage_count', 'desc')->take(5)->get();
        $this->command->info('Most popular tags:');
        foreach ($popularTags as $tag) {
            $this->command->info("  - {$tag->name} (used {$tag->usage_count} times)");
        }
    }
}
