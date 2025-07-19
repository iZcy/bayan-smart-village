<?php

namespace Database\Factories;

use App\Models\OfferTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfferTag>
 */
class OfferTagFactory extends Factory
{
    protected $model = OfferTag::class;

    public function definition(): array
    {
        $tags = [
            // Material tags
            'Bambu',
            'Kayu',
            'Rotan',
            'Pandan',
            'Keramik',
            'Kain',

            // Quality tags
            'Handmade',
            'Premium',
            'Limited Edition',
            'Eco-Friendly',
            'Organic',

            // Origin tags
            'Tradisional',
            'Lokal',
            'Asli',
            'Warisan',
            'Budaya',

            // Product type tags
            'Kerajinan',
            'Makanan',
            'Minuman',
            'Tekstil',
            'Aksesoris',

            // Feature tags
            'Tahan Lama',
            'Ringan',
            'Unik',
            'Mudah Dibersihkan',
            'Anti Air',

            // Style tags
            'Modern',
            'Klasik',
            'Minimalis',
            'Etnik',
            'Kontemporer',

            // Occasion tags
            'Souvenir',
            'Hadiah',
            'Koleksi',
            'Dekorasi',
            'Fungsional',

            // Certification tags
            'Halal',
            'BPOM',
            'SNI',
            'ISO',
            'Fair Trade',

            // Popular tags
            'Best Seller',
            'Trending',
            'Populer',
            'Rekomendasi',
            'Favorit'
        ];

        $name = $this->faker->randomElement($tags);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'usage_count' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Material tag.
     */
    public function material(): static
    {
        $materialTags = ['Bambu', 'Kayu', 'Rotan', 'Pandan', 'Keramik', 'Kain', 'Kulit', 'Logam'];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($materialTags),
        ]);
    }

    /**
     * Quality tag.
     */
    public function quality(): static
    {
        $qualityTags = ['Handmade', 'Premium', 'Limited Edition', 'Eco-Friendly', 'Organic', 'Berkualitas Tinggi'];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($qualityTags),
        ]);
    }

    /**
     * Popular tag with high usage count.
     */
    public function popular(): static
    {
        return $this->state(fn(array $attributes) => [
            'usage_count' => $this->faker->numberBetween(50, 500),
        ]);
    }

    /**
     * Trending tag.
     */
    public function trending(): static
    {
        $trendingTags = ['Trending', 'Populer', 'Best Seller', 'Favorit', 'Hot Item'];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($trendingTags),
            'usage_count' => $this->faker->numberBetween(20, 200),
        ]);
    }
}
