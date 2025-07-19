<?php

namespace Database\Seeders;

use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding villages...');

        // Create specific villages for better demo data
        $villages = [
            [
                'name' => 'Desa Wisata Penglipuran',
                'description' => 'Desa wisata tradisional Bali dengan arsitektur rumah adat yang masih terjaga dan kehidupan masyarakat yang harmonis dengan alam.',
                'domain' => 'penglipuran.com',
                'phone_number' => '+62 366 123456',
                'email' => 'info@penglipuran.com',
                'latitude' => -8.4385,
                'longitude' => 115.3733,
            ],
            [
                'name' => 'Desa Sade',
                'description' => 'Desa tradisional Sasak di Lombok yang mempertahankan kearifan lokal dalam arsitektur, budaya, dan kehidupan sehari-hari.',
                'phone_number' => '+62 370 987654',
                'email' => 'hello@desasade.id',
                'latitude' => -8.8913,
                'longitude' => 116.2687,
            ],
            [
                'name' => 'Desa Candirejo',
                'description' => 'Desa wisata di Magelang yang menawarkan pengalaman budaya Jawa dengan kegiatan pertanian dan kerajinan tradisional.',
                'phone_number' => '+62 293 456789',
                'email' => 'wisata@candirejo.id',
                'latitude' => -7.6053,
                'longitude' => 110.2073,
            ],
            [
                'name' => 'Desa Kemiren',
                'description' => 'Desa adat Using di Banyuwangi yang melestarikan tradisi dan budaya suku Using dengan berbagai ritual dan kesenian unik.',
                'phone_number' => '+62 333 111222',
                'email' => 'adat@kemiren.id',
                'latitude' => -8.4345,
                'longitude' => 114.2421,
            ],
            [
                'name' => 'Desa Wae Rebo',
                'description' => 'Desa tradisional Manggarai di Flores dengan rumah adat Mbaru Niang yang ikonik dan pemandangan alam yang menakjubkan.',
                'phone_number' => '+62 385 333444',
                'email' => 'contact@waerebo.id',
                'latitude' => -8.6833,
                'longitude' => 120.4167,
            ],
            [
                'name' => 'Desa Ngadas',
                'description' => 'Desa wisata di lereng Gunung Bromo dengan masyarakat Tengger yang masih menjaga tradisi dan budaya leluhur.',
                'phone_number' => '+62 341 555666',
                'email' => 'info@ngadas.id',
                'latitude' => -7.9425,
                'longitude' => 112.9532,
            ],
        ];

        foreach ($villages as $villageData) {
            Village::factory()
                ->active()
                ->create($villageData);
        }

        // Create additional random villages
        Village::factory()
            ->count(4)
            ->active()
            ->create();

        // Create some villages with custom domains
        Village::factory()
            ->count(2)
            ->active()
            ->withCustomDomain()
            ->create();

        // Create some inactive villages
        Village::factory()
            ->count(2)
            ->inactive()
            ->create();

        $this->command->info('Villages seeded successfully!');
    }
}
