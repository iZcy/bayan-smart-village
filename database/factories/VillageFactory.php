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
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Village::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(2, true),
            'domain' => null,
            'latitude' => fake()->latitude(-8.5, -8.0), // Lombok area coordinates
            'longitude' => fake()->longitude(116.0, 116.5), // Lombok area coordinates
            'phone_number' => fake()->phoneNumber(),
            'email' => Str::slug($name) . '@village.id',
            'address' => fake()->address() . ', Lombok Utara, NTB',
            'image_url' => 'https://picsum.photos/800/600?random=' . fake()->numberBetween(1000, 9999),
            'settings' => [
                'population' => fake()->numberBetween(500, 5000),
                'area_km2' => fake()->randomFloat(2, 5, 50),
                'primary_language' => fake()->randomElement(['Indonesian', 'Sasak', 'Balinese']),
                'main_occupation' => fake()->randomElement(['Agriculture', 'Tourism', 'Fishing', 'Crafts']),
                'tourist_attractions' => fake()->randomElements([
                    'Rice Terraces',
                    'Waterfalls',
                    'Beaches',
                    'Cultural Sites',
                    'Hiking Trails',
                    'Traditional Markets',
                    'Temples'
                ], fake()->numberBetween(2, 4)),
                'contact_person' => fake()->name(),
                'website_theme' => fake()->randomElement(['traditional', 'modern', 'nature']),
            ],
            'is_active' => true,
            'established_at' => fake()->dateTimeBetween('1970-01-01', '2000-12-31'), // Fixed date range
        ];
    }

    /**
     * Indicate that the village should have a custom domain.
     */
    public function withCustomDomain(): static
    {
        return $this->state(fn(array $attributes) => [
            'domain' => Str::slug($attributes['name']) . '.village.id',
        ]);
    }

    /**
     * Indicate that the village should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
