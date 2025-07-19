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

    private static $usedSlugs = [];

    public function definition(): array
    {
        $villageNames = [
            'Desa Makmur Sejahtera',
            'Desa Indah Permai',
            'Desa Sumber Rejeki',
            'Desa Taman Sari',
            'Desa Bunga Rampai',
            'Desa Sumber Makmur',
            'Desa Harapan Jaya',
            'Desa Maju Bersama',
            'Desa Karya Bhakti',
            'Desa Tunas Mekar',
            'Desa Cahaya Baru',
            'Desa Rimba Jaya',
            'Desa Tirta Sari',
            'Desa Puncak Indah',
            'Desa Bumi Asri',
            'Desa Sumber Hidup',
            'Desa Mandiri Sejahtera',
            'Desa Berkah Santosa',
            'Desa Gemah Ripah',
            'Desa Subur Makmur'
        ];

        $name = $this->getUniqueName($villageNames);
        $slug = $this->generateUniqueSlug($name);

        return [
            'name' => $name,
            'slug' => $slug,
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
     * Get a unique village name.
     */
    private function getUniqueName(array $names): string
    {
        // Create a pool of available names
        $usedNames = array_map(function ($slug) {
            return str_replace('-', ' ', ucwords($slug, ' -'));
        }, self::$usedSlugs);

        $availableNames = array_diff($names, $usedNames);

        // If no available names, create variations
        if (empty($availableNames)) {
            $baseName = $this->faker->randomElement($names);
            $suffixes = ['Baru', 'Utara', 'Selatan', 'Timur', 'Barat', 'Tengah', 'Atas', 'Bawah'];
            $suffix = $this->faker->randomElement($suffixes);
            $name = $baseName . ' ' . $suffix;

            // Make sure this variation is also unique
            $counter = 1;
            while (in_array(Str::slug($name), self::$usedSlugs)) {
                $name = $baseName . ' ' . $suffix . ' ' . $counter;
                $counter++;
            }

            return $name;
        }

        return $this->faker->randomElement($availableNames);
    }

    /**
     * Generate unique slug.
     */
    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Keep trying until we get a unique slug
        while (in_array($slug, self::$usedSlugs)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        self::$usedSlugs[] = $slug;
        return $slug;
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

    /**
     * Reset used slugs tracker (useful for testing).
     */
    public static function resetUsedSlugs(): void
    {
        self::$usedSlugs = [];
    }
}
