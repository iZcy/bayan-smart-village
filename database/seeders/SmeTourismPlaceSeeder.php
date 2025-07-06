<?php

namespace Database\Seeders;

use App\Models\SmeTourismPlace;
use App\Models\Category;
use Illuminate\Database\Seeder;

class SmeTourismPlaceSeeder extends Seeder
{
    public function run(): void
    {
        // Create 30 places with categories
        SmeTourismPlace::factory()
            ->count(30)
            ->create();

        // Create some places without location data
        SmeTourismPlace::factory()
            ->withoutLocation()
            ->count(5)
            ->create();

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
            ],
            [
                'name' => 'Warung Makan Bu Rudy',
                'description' => 'Warung makan legendaris yang menyajikan ayam taliwang dan plecing kangkung autentik khas Lombok. Sudah berdiri sejak 1979.',
                'address' => 'Jl. Diponegoro No. 123, Mataram',
                'latitude' => -8.5833,
                'longitude' => 116.1167,
                'category_type' => 'sme',
                'category_name' => 'Warung Makan',
            ],
            [
                'name' => 'Desa Wisata Sasak Sade',
                'description' => 'Desa wisata yang masih mempertahankan budaya dan tradisi Sasak asli. Pengunjung dapat belajar tentang kehidupan tradisional masyarakat Lombok.',
                'address' => 'Sade, Pujut, Lombok Tengah',
                'latitude' => -8.8833,
                'longitude' => 116.2833,
                'category_type' => 'tourism',
                'category_name' => 'Desa Wisata',
            ],
        ];

        foreach ($featuredPlaces as $placeData) {
            $category = Category::where('name', $placeData['category_name'])
                ->where('type', $placeData['category_type'])
                ->first();

            SmeTourismPlace::create([
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
        }
    }

    private function getCustomFieldsForPlace(string $type): array
    {
        if ($type === 'sme') {
            return [
                'opening_hours' => '08:00 - 22:00',
                'payment_methods' => ['Cash', 'Transfer Bank', 'QRIS'],
                'facilities' => ['WiFi Gratis', 'Parkir', 'AC', 'Toilet'],
                'speciality' => 'Ayam Taliwang dan Plecing Kangkung',
            ];
        } else {
            return [
                'best_time_to_visit' => 'Pagi hari (06:00-10:00)',
                'entrance_fee' => 'Rp 10.000',
                'facilities' => ['Toilet', 'Warung', 'Parkir', 'Spot Foto'],
                'difficulty_level' => 'Mudah',
            ];
        }
    }
}
