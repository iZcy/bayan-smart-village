<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Community>
 */
class CommunityFactory extends Factory
{
    protected $model = Community::class;

    private static $usedSlugs = [];

    public function definition(): array
    {
        $communityNames = [
            'Komunitas Kreatif Nusantara',
            'Kelompok Tani Makmur',
            'Paguyuban Seni Budaya',
            'Koperasi Maju Bersama',
            'Grup Kerajinan Lokal',
            'Asosiasi Wisata Desa',
            'Komunitas Pemuda Desa',
            'Kelompok Wanita Tani',
            'Paguyuban Pedagang',
            'Komunitas Batik Tradisional',
            'Kelompok Ternak Berkah',
            'Asosiasi Pengrajin',
            'Komunitas Kuliner Desa',
            'Kelompok Industri Rumahan',
            'Paguyuban Nelayan',
            'Komunitas Tukang Kayu',
            'Kelompok Peternak Mandiri',
            'Asosiasi Penenun',
            'Komunitas Fotografer Lokal',
            'Kelompok Ekonomi Kreatif'
        ];

        // Default village ID (will be overridden by forVillage method)
        $villageId = Village::factory()->create()->id;
        $name = $this->getUniqueNameForVillage($villageId, $communityNames);
        $slug = $this->generateUniqueSlug($villageId, $name);

        return [
            'village_id' => $villageId,
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->paragraph(2),
            'domain' => $this->faker->optional(0.2)->domainName(),
            'logo_url' => $this->faker->optional(0.6)->imageUrl(300, 300, 'business', true, 'logo'),
            'contact_person' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->email(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Get a unique community name for a specific village.
     */
    private function getUniqueNameForVillage(string $villageId, array $names): string
    {
        // Initialize village key if not exists
        if (!isset(self::$usedSlugs[$villageId])) {
            self::$usedSlugs[$villageId] = [];
        }

        // Get used names from slugs
        $usedNames = array_map(function ($slug) {
            return str_replace('-', ' ', ucwords($slug, ' -'));
        }, self::$usedSlugs[$villageId]);

        $availableNames = array_diff($names, $usedNames);

        // If no available names, create variations
        if (empty($availableNames)) {
            $baseName = $this->faker->randomElement($names);
            $suffixes = ['Mandiri', 'Bersatu', 'Sejahtera', 'Berkah', 'Jaya', 'Maju', 'Santosa'];
            $suffix = $this->faker->randomElement($suffixes);
            $name = $baseName . ' ' . $suffix;

            // Make sure this variation is also unique
            $counter = 1;
            while (in_array(Str::slug($name), self::$usedSlugs[$villageId])) {
                $name = $baseName . ' ' . $suffix . ' ' . $counter;
                $counter++;
            }

            return $name;
        }

        return $this->faker->randomElement($availableNames);
    }

    /**
     * Generate unique slug for village.
     */
    private function generateUniqueSlug(string $villageId, string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Initialize village key if not exists
        if (!isset(self::$usedSlugs[$villageId])) {
            self::$usedSlugs[$villageId] = [];
        }

        // Keep trying until we get a unique slug
        while (in_array($slug, self::$usedSlugs[$villageId])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Mark slug as used for this village
        self::$usedSlugs[$villageId][] = $slug;

        return $slug;
    }

    /**
     * Indicate that the community belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(function (array $attributes) use ($village) {
            $communityNames = [
                'Komunitas Kreatif Nusantara',
                'Kelompok Tani Makmur',
                'Paguyuban Seni Budaya',
                'Koperasi Maju Bersama',
                'Grup Kerajinan Lokal',
                'Asosiasi Wisata Desa',
                'Komunitas Pemuda Desa',
                'Kelompok Wanita Tani',
                'Paguyuban Pedagang',
                'Komunitas Batik Tradisional'
            ];

            $name = $this->getUniqueNameForVillage($village->id, $communityNames);
            $slug = $this->generateUniqueSlug($village->id, $name);

            return [
                'village_id' => $village->id,
                'name' => $name,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Indicate that the community is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the community is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the community has a custom domain.
     */
    public function withCustomDomain(): static
    {
        return $this->state(fn(array $attributes) => [
            'domain' => $this->faker->domainName(),
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
