<?php

namespace Database\Seeders;

use App\Models\SmeTourismPlace;
use App\Models\Category;
use App\Models\Village;
use Illuminate\Database\Seeder;

class SmeTourismPlaceSeeder extends Seeder
{
    public function run(): void
    {
        $villages = Village::active()->get();

        if ($villages->isEmpty()) {
            $this->command->warn('No active villages found. Please run VillageSeeder first.');
            return;
        }

        // Create 30 places with villages and categories
        for ($i = 0; $i < 30; $i++) {
            $village = $villages->random();
            SmeTourismPlace::factory()
                ->create([
                    'village_id' => $village->id,
                ]);
        }

        // Create some places without location data
        for ($i = 0; $i < 5; $i++) {
            $village = $villages->random();
            SmeTourismPlace::factory()
                ->withoutLocation()
                ->create([
                    'village_id' => $village->id,
                ]);
        }

        // Create specific featured places
        $featuredPlaces = [
            [
                'name' => 'Pantai Senggigi',
                'description' => 'Pantai terindah di Lombok dengan pemandangan sunset yang menakjubkan. Dilengkapi dengan berbagai fasilitas wisata dan akses yang mudah.',
                'address' => 'Senggigi, Batu Layar, Lombok Barat',
                'latitude' => -8.4811,
                'longitude' => 116.0425,
                'category_type' => 'tourism',
                'category_name' => 'Pantai',
                'village_name' => 'Senaru', // Try to link to Senaru village
            ],
            [
                'name' => 'Warung Makan Bu Rudy',
                'description' => 'Warung makan legendaris yang menyajikan ayam taliwang dan plecing kangkung autentik khas Lombok. Sudah berdiri sejak 1979.',
                'address' => 'Jl. Diponegoro No. 123, Mataram',
                'latitude' => -8.5833,
                'longitude' => 116.1167,
                'category_type' => 'sme',
                'category_name' => 'Warung Makan',
                'village_name' => 'Bayan Beleq', // Try to link to Bayan Beleq village
            ],
            [
                'name' => 'Desa Wisata Sasak Sade',
                'description' => 'Desa wisata yang masih mempertahankan budaya dan tradisi Sasak asli. Pengunjung dapat belajar tentang kehidupan tradisional masyarakat Lombok.',
                'address' => 'Sade, Pujut, Lombok Tengah',
                'latitude' => -8.8833,
                'longitude' => 116.2833,
                'category_type' => 'tourism',
                'category_name' => 'Desa Wisata',
                'village_name' => 'Pemenang', // Try to link to Pemenang village
            ],
        ];

        foreach ($featuredPlaces as $placeData) {
            $category = Category::where('name', $placeData['category_name'])
                ->where('type', $placeData['category_type'])
                ->first();

            // Try to find the specific village, fallback to random if not found
            $village = Village::where('name', $placeData['village_name'])->active()->first()
                ?? $villages->random();

            SmeTourismPlace::create([
                'village_id' => $village->id,
                'name' => $placeData['name'],
                'description' => $placeData['description'],
                'address' => $placeData['address'],
                'latitude' => $placeData['latitude'],
                'longitude' => $placeData['longitude'],
                'phone_number' => '+62 81' . fake()->randomNumber(8, true),
                'image_url' => 'https://picsum.photos/800/600?random=' . fake()->numberBetween(100, 200),
                'category_id' => $category->id,
                'custom_fields' => $this->getCustomFieldsForPlace($placeData['category_type']),
            ]);

            $this->command->info("✓ Created featured place: {$placeData['name']} in village: {$village->name}");
        }

        $totalPlaces = SmeTourismPlace::count();
        $placesWithVillages = SmeTourismPlace::whereNotNull('village_id')->count();

        $this->command->info("✅ Place seeding completed!");
        $this->command->info("   Total places: {$totalPlaces}");
        $this->command->info("   Places linked to villages: {$placesWithVillages}");
    }

    private function getCustomFieldsForPlace(string $type): array
    {
        if ($type === 'sme') {
            return [
                'opening_hours' => fake()->randomElement([
                    '08:00 - 17:00',
                    '09:00 - 21:00',
                    '24 jam',
                    '06:00 - 22:00'
                ]),
                'payment_methods' => fake()->randomElements([
                    'Cash',
                    'Transfer Bank',
                    'QRIS',
                    'GoPay',
                    'OVO',
                    'Dana'
                ], fake()->numberBetween(2, 4)),
                'facilities' => fake()->randomElements([
                    'WiFi Gratis',
                    'Parkir',
                    'AC',
                    'Toilet',
                    'Mushola',
                    'Delivery'
                ], fake()->numberBetween(2, 3)),
                'speciality' => fake()->sentence(3),
            ];
        } else {
            return [
                'best_time_to_visit' => fake()->randomElement([
                    'Pagi hari (06:00-10:00)',
                    'Sore hari (16:00-18:00)',
                    'Sepanjang hari',
                    'Malam hari (19:00-22:00)'
                ]),
                'entrance_fee' => fake()->randomElement([
                    'Gratis',
                    'Rp 5.000',
                    'Rp 10.000',
                    'Rp 15.000',
                    'Rp 25.000'
                ]),
                'facilities' => fake()->randomElements([
                    'Toilet',
                    'Warung',
                    'Parkir',
                    'Gazebo',
                    'Mushola',
                    'Spot Foto'
                ], fake()->numberBetween(2, 4)),
                'difficulty_level' => fake()->randomElement([
                    'Mudah',
                    'Sedang',
                    'Sulit',
                    'Sangat Sulit'
                ]),
            ];
        }
    }
}
