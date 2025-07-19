<?php

namespace Database\Factories;

use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Village>
 */
class VillageFactory extends Factory
{
    protected $model = Village::class;

    public function definition(): array
    {
        $name = $this->faker->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(3),
            'domain' => $this->faker->optional(0.3)->domainName(),
            'latitude' => $this->faker->latitude(-10, -6), // Indonesia latitude range
            'longitude' => $this->faker->longitude(95, 141), // Indonesia longitude range
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'image_url' => $this->faker->optional(0.7)->imageUrl(800, 600, 'nature', true, 'village'),
            'settings' => [
                'maintenance_mode' => false,
                'theme_color' => $this->faker->hexColor(),
                'featured_image_count' => $this->faker->numberBetween(3, 10),
            ],
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
            'established_at' => $this->faker->dateTimeBetween('-50 years', '-1 year'),
        ];
    }

    /**
     * Indicate that the village is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the village is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the village has a custom domain.
     */
    public function withCustomDomain(): static
    {
        return $this->state(fn(array $attributes) => [
            'domain' => $this->faker->domainName(),
        ]);
    }

    /**
     * Indicate that the village has no custom domain.
     */
    public function withoutCustomDomain(): static
    {
        return $this->state(fn(array $attributes) => [
            'domain' => null,
        ]);
    }
}
