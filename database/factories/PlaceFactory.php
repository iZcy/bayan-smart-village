<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    protected $model = Place::class;

    private static $usedSlugs = [];

    public function definition(): array
    {
        $placeTypes = [
            'Objek Wisata',
            'Tempat Bersejarah',
            'Pantai',
            'Gunung',
            'Air Terjun',
            'Candi',
            'Museum',
            'Taman',
            'Danau',
            'Goa',
            'Pasar Tradisional',
            'Rumah Adat',
            'Masjid Bersejarah',
            'Gereja Tua',
            'Monumen',
            'Bendungan',
            'Hutan Wisata',
            'Bukit',
            'Lembah',
            'Sungai',
            'Mata Air',
            'Kebun Raya'
        ];

        $placeNames = [
            'Indah',
            'Cantik',
            'Asri',
            'Permai',
            'Sejuk',
            'Teduh',
            'Damai',
            'Tenang',
            'Eksotis',
            'Menawan',
            'Spektakuler',
            'Megah',
            'Anggun',
            'Elok',
            'Syahdu'
        ];

        $type = $this->faker->randomElement($placeTypes);
        $adjective = $this->faker->randomElement($placeNames);
        $location = $this->faker->city();

        $name = $type . ' ' . $adjective . ' ' . $location;

        // Default village ID (will be overridden by forVillage method)
        $villageId = Village::factory()->create()->id;
        $slug = $this->generateUniqueSlug($villageId, $name);

        $categoryId = null;
        if ($this->faker->boolean(70)) { // 70% chance of having a category
            $categories = \App\Models\Category::where('village_id', $villageId)->pluck('id');
            if ($categories->isNotEmpty()) {
                $categoryId = $this->faker->randomElement($categories->toArray());
            }
        }

        return [
            'village_id' => $villageId,
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->paragraphs(3, true),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(-10, -6),
            'longitude' => $this->faker->longitude(95, 141),
            'phone_number' => $this->faker->optional(0.6)->phoneNumber(),
            'image_url' => $this->faker->optional(0.8)->imageUrl(800, 600),
            'custom_fields' => [
                'opening_hours' => $this->faker->randomElement([
                    '08:00 - 17:00',
                    '09:00 - 16:00',
                    '24 Jam',
                    'Sesuai jadwal ibadah'
                ]),
                'entrance_fee' => $this->faker->optional(0.7)->randomElement([
                    'Gratis',
                    'Rp 5.000',
                    'Rp 10.000',
                    'Rp 15.000',
                    'Rp 25.000'
                ]),
                'facilities' => $this->faker->randomElements([
                    'Toilet',
                    'Mushola',
                    'Parkir',
                    'Warung',
                    'Gazebo',
                    'Area Piknik',
                    'Pemandu Wisata'
                ], $this->faker->numberBetween(1, 4)),
                'best_time_to_visit' => $this->faker->randomElement([
                    'Pagi hari',
                    'Sore hari',
                    'Musim kemarau',
                    'Sepanjang tahun'
                ]),
            ],
        ];
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
     * Indicate that the place belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(function (array $attributes) use ($village) {
            $slug = $this->generateUniqueSlug($village->id, $attributes['name']);

            return [
                'village_id' => $village->id,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Tourism place type.
     */
    public function tourism(): static
    {
        $tourismTypes = [
            'Pantai Indah',
            'Air Terjun Cantik',
            'Bukit Sunrise',
            'Danau Tenang',
            'Goa Misterius',
            'Hutan Bambu',
            'Pemandian Air Panas',
            'Taman Wisata'
        ];

        return $this->state(function (array $attributes) use ($tourismTypes) {
            $name = $this->faker->randomElement($tourismTypes) . ' ' . $this->faker->city();
            $villageId = $attributes['village_id'] ?? Village::factory()->create()->id;
            $slug = $this->generateUniqueSlug($villageId, $name);

            return [
                'name' => $name,
                'slug' => $slug,
                'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                    'type' => 'tourism',
                    'difficulty_level' => $this->faker->randomElement(['Mudah', 'Sedang', 'Sulit']),
                ]),
            ];
        });
    }

    /**
     * Historical place type.
     */
    public function historical(): static
    {
        $historicalTypes = [
            'Candi Kuno',
            'Benteng Bersejarah',
            'Rumah Adat',
            'Makam Keramat',
            'Monumen Pahlawan',
            'Museum Daerah',
            'Istana Raja',
            'Situs Bersejarah'
        ];

        return $this->state(function (array $attributes) use ($historicalTypes) {
            $name = $this->faker->randomElement($historicalTypes) . ' ' . $this->faker->city();
            $villageId = $attributes['village_id'] ?? Village::factory()->create()->id;
            $slug = $this->generateUniqueSlug($villageId, $name);

            return [
                'name' => $name,
                'slug' => $slug,
                'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                    'type' => 'historical',
                    'built_year' => $this->faker->year('-100 years'),
                ]),
            ];
        });
    }

    /**
     * Religious place type.
     */
    public function religious(): static
    {
        $religiousTypes = [
            'Masjid Agung',
            'Gereja Tua',
            'Pura Suci',
            'Vihara Damai',
            'Klenteng Bersejarah',
            'Makam Wali',
            'Pesantren Kuno',
            'Surau Bersejarah'
        ];

        return $this->state(function (array $attributes) use ($religiousTypes) {
            $name = $this->faker->randomElement($religiousTypes) . ' ' . $this->faker->city();
            $villageId = $attributes['village_id'] ?? Village::factory()->create()->id;
            $slug = $this->generateUniqueSlug($villageId, $name);

            return [
                'name' => $name,
                'slug' => $slug,
                'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                    'type' => 'religious',
                    'religion' => $this->faker->randomElement(['Islam', 'Kristen', 'Hindu', 'Buddha']),
                ]),
            ];
        });
    }

    /**
     * Reset used slugs tracker (useful for testing).
     */
    public static function resetUsedSlugs(): void
    {
        self::$usedSlugs = [];
    }
}
