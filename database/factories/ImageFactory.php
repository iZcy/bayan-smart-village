<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition(): array
    {
        $captions = [
            'Pemandangan Alam',
            'Sunrise di Bukit',
            'Air Terjun Indah',
            'Hamparan Sawah',
            'Tarian Tradisional',
            'Upacara Adat',
            'Kerajinan Lokal',
            'Festival Budaya',
            'Kuliner Khas',
            'Makanan Tradisional',
            'Jajanan Lokal',
            'Proses Memasak',
            'Produk UMKM',
            'Proses Produksi',
            'Workshop Kerajinan',
            'Pameran Produk',
            'Destinasi Wisata',
            'Aktivitas Wisata',
            'Fasilitas Wisata',
            'Wisatawan'
        ];

        $caption = $this->faker->randomElement($captions);

        return [
            'village_id' => $this->faker->optional(0.5)->randomElement(Village::pluck('id')->toArray() ?: [Village::factory()->create()->id]),
            'community_id' => $this->faker->optional(0.3)->randomElement(Community::pluck('id')->toArray() ?: [null]),
            'sme_id' => $this->faker->optional(0.3)->randomElement(Sme::pluck('id')->toArray() ?: [null]),
            'place_id' => $this->faker->optional(0.4)->randomElement(Place::pluck('id')->toArray() ?: [null]),
            'image_url' => $this->faker->imageUrl(800, 600),
            'caption' => $caption,
            'alt_text' => $this->faker->optional(0.8)->sentence(3),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_featured' => $this->faker->boolean(30), // 30% chance of being featured
        ];
    }

    /**
     * Indicate that the image belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => $village->id,
            'community_id' => null,
            'sme_id' => null,
            'place_id' => null,
        ]);
    }

    /**
     * Indicate that the image belongs to a specific community.
     */
    public function forCommunity(Community $community): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => null,
            'community_id' => $community->id,
            'sme_id' => null,
            'place_id' => null,
        ]);
    }

    /**
     * Indicate that the image belongs to a specific SME.
     */
    public function forSme(Sme $sme): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => null,
            'community_id' => null,
            'sme_id' => $sme->id,
            'place_id' => null,
        ]);
    }

    /**
     * Indicate that the image belongs to a specific place.
     */
    public function forPlace(Place $place): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => null,
            'community_id' => null,
            'sme_id' => null,
            'place_id' => $place->id,
        ]);
    }

    /**
     * Featured image.
     */
    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Nature category image.
     */
    public function nature(): static
    {
        $captions = ['Pemandangan Alam', 'Sunrise di Bukit', 'Air Terjun Indah', 'Hamparan Sawah'];

        return $this->state(fn(array $attributes) => [
            'image_url' => $this->faker->imageUrl(800, 600),
            'caption' => $this->faker->randomElement($captions),
        ]);
    }

    /**
     * Culture category image.
     */
    public function culture(): static
    {
        $captions = ['Tarian Tradisional', 'Upacara Adat', 'Kerajinan Lokal', 'Festival Budaya'];

        return $this->state(fn(array $attributes) => [
            'image_url' => $this->faker->imageUrl(800, 600),
            'caption' => $this->faker->randomElement($captions),
        ]);
    }

    /**
     * Food category image.
     */
    public function food(): static
    {
        $captions = ['Kuliner Khas', 'Makanan Tradisional', 'Jajanan Lokal', 'Proses Memasak'];

        return $this->state(fn(array $attributes) => [
            'image_url' => $this->faker->imageUrl(800, 600),
            'caption' => $this->faker->randomElement($captions),
        ]);
    }

    /**
     * Business category image.
     */
    public function business(): static
    {
        $captions = ['Produk UMKM', 'Proses Produksi', 'Workshop Kerajinan', 'Pameran Produk'];

        return $this->state(fn(array $attributes) => [
            'image_url' => $this->faker->imageUrl(800, 600),
            'caption' => $this->faker->randomElement($captions),
        ]);
    }

    /**
     * Tourism category image.
     */
    public function tourism(): static
    {
        $captions = ['Destinasi Wisata', 'Aktivitas Wisata', 'Fasilitas Wisata', 'Wisatawan'];

        return $this->state(fn(array $attributes) => [
            'image_url' => $this->faker->imageUrl(800, 600),
            'caption' => $this->faker->randomElement($captions),
        ]);
    }

    /**
     * Gallery image with specific sort order.
     */
    public function gallery(int $sortOrder = 0): static
    {
        return $this->state(fn(array $attributes) => [
            'sort_order' => $sortOrder ?? $this->faker->numberBetween(1, 50),
            'is_featured' => false,
        ]);
    }
}
