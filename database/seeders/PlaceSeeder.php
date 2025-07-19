<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\Village;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding places...');

        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        foreach ($villages as $village) {
            $this->command->info("Creating places for {$village->name}");

            // Create tourism places
            Place::factory()
                ->count(rand(1, 3))
                ->forVillage($village)
                ->tourism()
                ->create();

            // Create historical places
            Place::factory()
                ->count(rand(1, 2))
                ->forVillage($village)
                ->historical()
                ->create();

            // Create religious places
            Place::factory()
                ->count(rand(0, 2))
                ->forVillage($village)
                ->religious()
                ->create();
        }

        $this->command->info('Places seeded successfully!');
        $this->command->info('Total places created: ' . Place::count());
    }
}
