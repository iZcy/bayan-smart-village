<?php

namespace Database\Factories;

use App\Models\Sme;
use App\Models\Community;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sme>
 */
class SmeFactory extends Factory
{
    protected $model = Sme::class;

    private static $usedSlugs = [];

    public function definition(): array
    {
        $productBusinesses = [
            'Kerajinan Bambu Berkah',
            'Tenun Indah Nusantara',
            'Batik Tulis Asli',
            'Keramik Cantik Desa',
            'Madu Murni Pegunungan',
            'Kopi Arabika Premium',
            'Dodol Khas Daerah',
            'Emping Melinjo Crispy',
            'Keripik Singkong Gurih',
            'Anyaman Pandan Kreatif',
            'Ukiran Kayu Artistik',
            'Songket Emas',
            'Rempah Tradisional',
            'Gula Aren Murni',
            'Teh Herbal Alami',
            'Kain Tenun Ikat',
            'Patung Kayu Jati',
            'Tas Rajut Handmade',
            'Aksesoris Perak',
            'Minyak Kelapa Murni'
        ];

        $serviceBusinesses = [
            'Wisata Desa Adventure',
            'Homestay Keluarga Bahagia',
            'Warung Nasi Gudeg Bu Sri',
            'Ojek Wisata Friendly',
            'Jasa Foto Prewedding',
            'Spa Tradisional Sehat',
            'Pelatihan Kerajinan Lokal',
            'Bengkel Motor Jujur',
            'Salon Kecantikan Modern',
            'Catering Masakan Daerah',
            'Laundry Kilat Bersih',
            'Kursus Bahasa Inggris',
            'Jasa Pijat Refleksi',
            'Tour Guide Profesional',
            'Rental Motor Harian',
            'Warung Kopi Tradisional',
            'Jasa Dokumentasi Event',
            'Kursus Mengemudi',
            'Jasa Cleaning Service',
            'Warung Soto Ayam'
        ];

        $type = $this->faker->randomElement(['product', 'service']);
        $businesses = $type === 'product' ? $productBusinesses : $serviceBusinesses;

        // Get community ID (will be overridden by state methods)
        $communityId = Community::factory()->create()->id;

        // Get unique name and slug for this community
        $name = $this->getUniqueNameForCommunity($communityId, $businesses);
        $slug = $this->generateUniqueSlug($communityId, $name);

        return [
            'community_id' => $communityId,
            'place_id' => $this->faker->optional(0.4)->randomElement(Place::pluck('id')->toArray() ?: [null]),
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->paragraphs(2, true),
            'type' => $type,
            'owner_name' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->optional(0.7)->email(),
            'logo_url' => $this->faker->optional(0.5)->imageUrl(300, 300),
            'business_hours' => [
                'monday' => '08:00 - 17:00',
                'tuesday' => '08:00 - 17:00',
                'wednesday' => '08:00 - 17:00',
                'thursday' => '08:00 - 17:00',
                'friday' => '08:00 - 17:00',
                'saturday' => '08:00 - 15:00',
                'sunday' => $this->faker->randomElement(['Tutup', '09:00 - 14:00']),
            ],
            'is_verified' => $this->faker->boolean(60), // 60% chance of being verified
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
        ];
    }

    /**
     * Get a unique business name for a specific community.
     */
    private function getUniqueNameForCommunity(string $communityId, array $businesses): string
    {
        // Initialize community key if not exists
        if (!isset(self::$usedSlugs[$communityId])) {
            self::$usedSlugs[$communityId] = [];
        }

        // Get available business names for this community
        $usedNames = array_map(function ($slug) {
            return str_replace('-', ' ', ucwords($slug, ' -'));
        }, self::$usedSlugs[$communityId]);

        $availableBusinesses = array_diff($businesses, $usedNames);

        // If no available business names, add random suffix to existing ones
        if (empty($availableBusinesses)) {
            $baseName = $this->faker->randomElement($businesses);
            $suffix = $this->faker->randomElement(['Sejahtera', 'Mandiri', 'Bersama', 'Makmur', 'Jaya', 'Sukses', 'Berkah', 'Sentosa']);
            return $baseName . ' ' . $suffix;
        }

        // Pick a random available business name
        return $this->faker->randomElement($availableBusinesses);
    }

    /**
     * Generate unique slug for community.
     */
    private function generateUniqueSlug(string $communityId, string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Initialize community key if not exists
        if (!isset(self::$usedSlugs[$communityId])) {
            self::$usedSlugs[$communityId] = [];
        }

        // Keep trying until we get a unique slug
        while (in_array($slug, self::$usedSlugs[$communityId])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Mark slug as used for this community
        self::$usedSlugs[$communityId][] = $slug;

        return $slug;
    }

    /**
     * Indicate that the SME belongs to a specific community.
     */
    public function forCommunity(Community $community): static
    {
        return $this->state(function (array $attributes) use ($community) {
            // Get unique name and slug for this specific community
            $type = $attributes['type'] ?? 'product';

            $productBusinesses = [
                'Kerajinan Bambu Berkah',
                'Tenun Indah Nusantara',
                'Batik Tulis Asli',
                'Keramik Cantik Desa',
                'Madu Murni Pegunungan',
                'Kopi Arabika Premium',
                'Dodol Khas Daerah',
                'Emping Melinjo Crispy',
                'Keripik Singkong Gurih',
                'Anyaman Pandan Kreatif',
                'Ukiran Kayu Artistik',
                'Songket Emas'
            ];

            $serviceBusinesses = [
                'Wisata Desa Adventure',
                'Homestay Keluarga Bahagia',
                'Warung Nasi Gudeg Bu Sri',
                'Ojek Wisata Friendly',
                'Jasa Foto Prewedding',
                'Spa Tradisional Sehat',
                'Pelatihan Kerajinan Lokal',
                'Bengkel Motor Jujur',
                'Salon Kecantikan Modern',
                'Catering Masakan Daerah',
                'Laundry Kilat Bersih',
                'Kursus Bahasa Inggris'
            ];

            $businesses = $type === 'product' ? $productBusinesses : $serviceBusinesses;
            $name = $this->getUniqueNameForCommunity($community->id, $businesses);
            $slug = $this->generateUniqueSlug($community->id, $name);

            return [
                'community_id' => $community->id,
                'name' => $name,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Indicate that the SME is located at a specific place.
     */
    public function atPlace(Place $place): static
    {
        return $this->state(fn(array $attributes) => [
            'place_id' => $place->id,
        ]);
    }

    /**
     * Product-focused SME.
     */
    public function product(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'product',
        ]);
    }

    /**
     * Service-focused SME.
     */
    public function service(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'service',
        ]);
    }

    /**
     * Verified SME.
     */
    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Unverified SME.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_verified' => false,
        ]);
    }

    /**
     * Active SME.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Inactive SME.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
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
