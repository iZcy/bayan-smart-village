<?php

namespace Database\Seeders;

use App\Models\Village;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating villages...');

        // Create some featured villages first
        $featuredVillages = [
            [
                'name' => 'Bayan',
                'slug' => 'bayan',
                'description' => 'Desa wisata dengan keindahan alam yang memukau dan budaya Sasak yang masih terjaga. Terkenal dengan pemandangan gunung Rinjani dan kehidupan tradisional masyarakat Sasak.',
                'latitude' => -8.3500,
                'longitude' => 116.4000,
                'phone_number' => '+62 81234567001',
                'email' => 'info@bayan.village.id',
                'address' => 'Bayan, Bayan, Lombok Utara, NTB',
                'is_active' => true,
                'established_at' => Carbon::create(1980, 1, 1), // Changed to 1980
                'settings' => [
                    'population' => 2500,
                    'area_km2' => 15.5,
                    'primary_language' => 'Sasak',
                    'main_occupation' => 'Agriculture',
                    'tourist_attractions' => ['Rice Terraces', 'Cultural Sites', 'Hiking Trails'],
                    'contact_person' => 'Kepala Desa Bayan Beleq',
                    'website_theme' => 'traditional',
                ],
            ],
            [
                'name' => 'Senaru',
                'slug' => 'senaru',
                'description' => 'Pintu gerbang utama pendakian Gunung Rinjani dengan air terjun Sendang Gile dan Tiu Kelep yang menawan. Desa ini menawarkan pengalaman ekowisata yang tak terlupakan.',
                'latitude' => -8.3167,
                'longitude' => 116.4167,
                'phone_number' => '+62 81234567002',
                'email' => 'info@senaru.village.id',
                'address' => 'Senaru, Bayan, Lombok Utara, NTB',
                'is_active' => true,
                'established_at' => Carbon::create(1975, 8, 17), // Changed to 1975
                'settings' => [
                    'population' => 1800,
                    'area_km2' => 22.3,
                    'primary_language' => 'Sasak',
                    'main_occupation' => 'Tourism',
                    'tourist_attractions' => ['Waterfalls', 'Hiking Trails', 'Traditional Markets'],
                    'contact_person' => 'Kepala Desa Senaru',
                    'website_theme' => 'nature',
                ],
            ],
            [
                'name' => 'Pemenang',
                'slug' => 'pemenang',
                'description' => 'Desa pelabuhan yang menjadi gerbang menuju Gili Trawangan, Gili Meno, dan Gili Air. Memiliki pantai yang indah dan kehidupan nelayan yang masih autentik.',
                'latitude' => -8.3833,
                'longitude' => 116.1167,
                'phone_number' => '+62 81234567003',
                'email' => 'info@pemenang.village.id',
                'address' => 'Pemenang, Pemenang, Lombok Utara, NTB',
                'is_active' => true,
                'established_at' => Carbon::create(1985, 12, 25), // Changed to 1985
                'settings' => [
                    'population' => 3200,
                    'area_km2' => 18.7,
                    'primary_language' => 'Sasak',
                    'main_occupation' => 'Fishing',
                    'tourist_attractions' => ['Beaches', 'Traditional Markets', 'Cultural Sites'],
                    'contact_person' => 'Kepala Desa Pemenang',
                    'website_theme' => 'modern',
                ],
            ],
        ];

        foreach ($featuredVillages as $villageData) {
            Village::create([
                'name' => $villageData['name'],
                'slug' => $villageData['slug'],
                'description' => $villageData['description'],
                'latitude' => $villageData['latitude'],
                'longitude' => $villageData['longitude'],
                'phone_number' => $villageData['phone_number'],
                'email' => $villageData['email'],
                'address' => $villageData['address'],
                'image_url' => 'https://picsum.photos/800/600?random=' . fake()->numberBetween(100, 300),
                'settings' => $villageData['settings'],
                'is_active' => $villageData['is_active'],
                'established_at' => $villageData['established_at'],
            ]);

            $this->command->info("  ✓ Created featured village: {$villageData['name']}");
        }

        // Create additional random villages
        $randomVillages = Village::factory()
            ->count(7)
            ->create();

        $this->command->info("  ✓ Created {$randomVillages->count()} additional villages");

        // Create some villages with custom domains
        $customDomainVillages = Village::factory()
            ->withCustomDomain()
            ->count(2)
            ->create();

        $this->command->info("  ✓ Created {$customDomainVillages->count()} villages with custom domains");

        // Create some inactive villages
        $inactiveVillages = Village::factory()
            ->inactive()
            ->count(1)
            ->create();

        $this->command->info("  ✓ Created {$inactiveVillages->count()} inactive village");

        $totalVillages = Village::count();
        $this->command->info("✅ Village seeding completed! Total villages: {$totalVillages}");
    }
}
