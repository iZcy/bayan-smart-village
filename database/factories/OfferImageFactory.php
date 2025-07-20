<?php

namespace Database\Factories;

use App\Models\OfferImage;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfferImage>
 */
class OfferImageFactory extends Factory
{
    protected $model = OfferImage::class;

    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'image_url' => 'https://picsum.photos/800/600?random=' . $this->faker->numberBetween(1, 1000),
            'alt_text' => $this->faker->optional(0.8)->sentence(3),
            'sort_order' => $this->faker->numberBetween(0, 20),
            'is_primary' => false, // Will be set to true for one image per offer in seeder
        ];
    }

    /**
     * Indicate that the image belongs to a specific offer.
     */
    public function forOffer(Offer $offer): static
    {
        return $this->state(fn(array $attributes) => [
            'offer_id' => $offer->id,
        ]);
    }

    /**
     * Primary image (main product image).
     */
    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => true,
            'sort_order' => 0,
            'alt_text' => $this->faker->sentence(3),
            'image_url' => 'https://picsum.photos/600/400?random=' . $this->faker->numberBetween(1, 1000),
        ]);
    }

    /**
     * Secondary image (additional product images).
     */
    public function secondary(int $sortOrder = 0): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => false,
            'sort_order' => $sortOrder ?? $this->faker->numberBetween(1, 10),
            'alt_text' => $this->faker->sentence(3),
        ]);
    }

    /**
     * Gallery image with specific order.
     */
    public function gallery(int $order): static
    {
        return $this->state(fn(array $attributes) => [
            'sort_order' => $order,
            'is_primary' => $order === 0,
        ]);
    }
}
