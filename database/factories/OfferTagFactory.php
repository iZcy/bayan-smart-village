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

    private static $usedNames = [];
    private static $usedSlugs = [];

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
            'Kulit',
            'Logam',
            'Perak',
            'Emas',
            'Tembaga',
            'Besi',
            'Aluminium',
            'Plastik',
            'Karet',

            // Quality tags
            'Handmade',
            'Premium',
            'Limited Edition',
            'Eco-Friendly',
            'Organic',
            'Berkualitas Tinggi',
            'Eksklusif',
            'Mewah',
            'Elegan',
            'Sophisticated',

            // Origin tags
            'Tradisional',
            'Lokal',
            'Asli',
            'Warisan',
            'Budaya',
            'Nusantara',
            'Jawa',
            'Bali',
            'Sumatera',
            'Kalimantan',
            'Sulawesi',
            'Papua',

            // Product type tags
            'Kerajinan',
            'Makanan',
            'Minuman',
            'Tekstil',
            'Aksesoris',
            'Furniture',
            'Dekorasi',
            'Souvenir',
            'Fashion',
            'Perhiasan',
            'Batik',
            'Tenun',

            // Feature tags
            'Tahan Lama',
            'Ringan',
            'Unik',
            'Mudah Dibersihkan',
            'Anti Air',
            'Fleksibel',
            'Kuat',
            'Halus',
            'Kasar',
            'Transparan',
            'Berwarna',

            // Style tags
            'Modern',
            'Klasik',
            'Minimalis',
            'Etnik',
            'Kontemporer',
            'Vintage',
            'Rustic',
            'Industrial',
            'Bohemian',
            'Skandinavia',
            'Tropical',

            // Occasion tags
            'Souvenir',
            'Hadiah',
            'Koleksi',
            'Dekorasi',
            'Fungsional',
            'Ceremonial',
            'Pernikahan',
            'Ulang Tahun',
            'Wisuda',
            'Lebaran',
            'Natal',
            'Imlek',

            // Certification tags
            'Halal',
            'BPOM',
            'SNI',
            'ISO',
            'Fair Trade',
            'HACCP',
            'Organic Certified',
            'FDA Approved',
            'CE Marked',
            'Eco Label',

            // Popular tags
            'Best Seller',
            'Trending',
            'Populer',
            'Rekomendasi',
            'Favorit',
            'Hot Item',
            'New Arrival',
            'Staff Pick',
            'Customer Choice',
            'Award Winner',

            // Size tags
            'Mini',
            'Kecil',
            'Sedang',
            'Besar',
            'Jumbo',
            'XS',
            'S',
            'M',
            'L',
            'XL',

            // Color tags
            'Merah',
            'Biru',
            'Hijau',
            'Kuning',
            'Ungu',
            'Oranye',
            'Pink',
            'Coklat',
            'Hitam',
            'Putih',
            'Abu-abu',
            'Natural',
            'Emas',
            'Perak',
            'Perunggu'
        ];

        $name = $this->getUniqueName($tags);
        $slug = $this->generateUniqueSlug($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'usage_count' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Get a unique tag name.
     */
    private function getUniqueName(array $tags): string
    {
        $availableTags = array_diff($tags, self::$usedNames);

        // If no available tags, create variations
        if (empty($availableTags)) {
            $baseName = $this->faker->randomElement($tags);
            $suffixes = ['Premium', 'Spesial', 'Eksklusif', 'Plus', 'Pro', 'Deluxe', 'Classic', 'Modern'];
            $suffix = $this->faker->randomElement($suffixes);
            $name = $baseName . ' ' . $suffix;

            // Make sure this variation is also unique
            $counter = 1;
            while (in_array($name, self::$usedNames)) {
                $name = $baseName . ' ' . $suffix . ' ' . $counter;
                $counter++;
            }

            self::$usedNames[] = $name;
            return $name;
        }

        $selectedName = $this->faker->randomElement($availableTags);
        self::$usedNames[] = $selectedName;
        return $selectedName;
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
     * Material tag.
     */
    public function material(): static
    {
        $materialTags = ['Bambu', 'Kayu', 'Rotan', 'Pandan', 'Keramik', 'Kain', 'Kulit', 'Logam'];

        return $this->state(function (array $attributes) use ($materialTags) {
            $name = $this->getUniqueName($materialTags);
            $slug = $this->generateUniqueSlug($name);

            return [
                'name' => $name,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Quality tag.
     */
    public function quality(): static
    {
        $qualityTags = ['Handmade', 'Premium', 'Limited Edition', 'Eco-Friendly', 'Organic', 'Berkualitas Tinggi'];

        return $this->state(function (array $attributes) use ($qualityTags) {
            $name = $this->getUniqueName($qualityTags);
            $slug = $this->generateUniqueSlug($name);

            return [
                'name' => $name,
                'slug' => $slug,
            ];
        });
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

        return $this->state(function (array $attributes) use ($trendingTags) {
            $name = $this->getUniqueName($trendingTags);
            $slug = $this->generateUniqueSlug($name);

            return [
                'name' => $name,
                'slug' => $slug,
                'usage_count' => $this->faker->numberBetween(20, 200),
            ];
        });
    }

    /**
     * Reset used trackers (useful for testing).
     */
    public static function resetUsedTags(): void
    {
        self::$usedNames = [];
        self::$usedSlugs = [];
    }
}
