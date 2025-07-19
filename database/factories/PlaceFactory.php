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
            'Monumen'
        ];

        $name = $this->faker->randomElement($placeTypes) . ' ' . $this->faker->words(2, true);

        return [
            'village_id' => Village::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(3, true),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(-10, -6),
            'longitude' => $this->faker->longitude(95, 141),
            'phone_number' => $this->faker->optional(0.6)->phoneNumber(),
            'image_url' => $this->faker->optional(0.8)->imageUrl(800, 600, 'nature', true, 'place'),
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
     * Indicate that the place belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => $village->id,
        ]);
    }

    /**
     * Tourism place type.
     */
    public function tourism(): static
    {
        $tourismNames = [
            'Pantai Indah',
            'Air Terjun Cantik',
            'Bukit Sunrise',
            'Danau Tenang',
            'Goa Misterius',
            'Hutan Bambu',
            'Pemandian Air Panas'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($tourismNames) . ' ' . $this->faker->city(),
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'type' => 'tourism',
                'difficulty_level' => $this->faker->randomElement(['Mudah', 'Sedang', 'Sulit']),
            ]),
        ]);
    }

    /**
     * Historical place type.
     */
    public function historical(): static
    {
        $historicalNames = [
            'Candi Kuno',
            'Benteng Bersejarah',
            'Rumah Adat',
            'Makam Keramat',
            'Monumen Pahlawan',
            'Museum Daerah',
            'Istana Raja'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($historicalNames) . ' ' . $this->faker->city(),
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'type' => 'historical',
                'built_year' => $this->faker->year('-100 years'),
            ]),
        ]);
    }

    /**
     * Religious place type.
     */
    public function religious(): static
    {
        $religiousNames = [
            'Masjid Agung',
            'Gereja Tua',
            'Pura Suci',
            'Vihara Damai',
            'Klenteng Bersejarah',
            'Makam Wali',
            'Pesantren Kuno'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($religiousNames) . ' ' . $this->faker->city(),
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'type' => 'religious',
                'religion' => $this->faker->randomElement(['Islam', 'Kristen', 'Hindu', 'Buddha']),
            ]),
        ]);
    }
}
