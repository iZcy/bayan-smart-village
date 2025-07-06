<?php

namespace Database\Factories;

use App\Models\SmeTourismPlace;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmeTourismPlaceFactory extends Factory
{
    protected $model = SmeTourismPlace::class;

    public function definition(): array
    {
        $lombokLocations = [
            'Mataram',
            'Senggigi',
            'Gili Trawangan',
            'Gili Air',
            'Gili Meno',
            'Kuta Lombok',
            'Selong Belanak',
            'Sekotong',
            'Bangsal',
            'Pemenang'
        ];

        $smeNames = [
            'Warung Bu Sari',
            'Kedai Kopi Lombok',
            'Toko Serba Ada Makmur',
            'Laundry Express',
            'Salon Cantik',
            'Bengkel Jaya Motor',
            'Toko Elektronik Maju',
            'Butik Fashion',
            'Apotek Sehat',
            'Toko Bangunan Berkah'
        ];

        $tourismNames = [
            'Pantai Senggigi',
            'Gunung Rinjani',
            'Air Terjun Sekumpul',
            'Gili Trawangan',
            'Pantai Kuta Lombok',
            'Desa Wisata Sasak',
            'Taman Narmada',
            'Pantai Tanjung Aan',
            'Bukit Merese',
            'Desa Sukarare'
        ];

        $category = Category::inRandomOrder()->first();

        if ($category && $category->type === 'sme') {
            $name = $this->faker->randomElement($smeNames);
        } else {
            $name = $this->faker->randomElement($tourismNames);
        }

        // Lombok coordinates range
        $latitude = $this->faker->randomFloat(8, -8.7, -8.1);  // Lombok latitude range
        $longitude = $this->faker->randomFloat(8, 115.8, 116.5); // Lombok longitude range

        return [
            'name' => $name,
            'description' => $this->faker->paragraphs(3, true),
            'address' => $this->faker->address . ', ' . $this->faker->randomElement($lombokLocations) . ', Lombok',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'phone_number' => '+62 81' . $this->faker->randomNumber(8, true),
            'image_url' => 'https://picsum.photos/800/600?random=' . $this->faker->numberBetween(1, 1000),
            'category_id' => $category ? $category->id : Category::factory(),
            'custom_fields' => $this->generateCustomFields($category ? $category->type : 'sme'),
        ];
    }

    private function generateCustomFields(string $type): array
    {
        if ($type === 'sme') {
            return [
                'opening_hours' => $this->faker->randomElement([
                    '08:00 - 17:00',
                    '09:00 - 21:00',
                    '24 jam',
                    '06:00 - 22:00'
                ]),
                'payment_methods' => $this->faker->randomElements([
                    'Cash',
                    'Transfer Bank',
                    'QRIS',
                    'GoPay',
                    'OVO',
                    'Dana'
                ], $this->faker->numberBetween(2, 4)),
                'facilities' => $this->faker->randomElements([
                    'WiFi Gratis',
                    'Parkir',
                    'AC',
                    'Toilet',
                    'Mushola',
                    'Delivery'
                ], $this->faker->numberBetween(2, 3)),
                'speciality' => $this->faker->sentence(3),
            ];
        } else {
            return [
                'best_time_to_visit' => $this->faker->randomElement([
                    'Pagi hari (06:00-10:00)',
                    'Sore hari (16:00-18:00)',
                    'Sepanjang hari',
                    'Malam hari (19:00-22:00)'
                ]),
                'entrance_fee' => $this->faker->randomElement([
                    'Gratis',
                    'Rp 5.000',
                    'Rp 10.000',
                    'Rp 15.000',
                    'Rp 25.000'
                ]),
                'facilities' => $this->faker->randomElements([
                    'Toilet',
                    'Warung',
                    'Parkir',
                    'Gazebo',
                    'Mushola',
                    'Spot Foto'
                ], $this->faker->numberBetween(2, 4)),
                'difficulty_level' => $this->faker->randomElement([
                    'Mudah',
                    'Sedang',
                    'Sulit',
                    'Sangat Sulit'
                ]),
            ];
        }
    }

    public function withoutLocation(): static
    {
        return $this->state(fn(array $attributes) => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}
