<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Village;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding categories...');

        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        foreach ($villages as $village) {
            $this->command->info("Creating categories for {$village->name}");

            // Create product categories
            Category::factory()
                ->count(rand(3, 5))
                ->forVillage($village)
                ->product()
                ->create();

            // Create service categories
            Category::factory()
                ->count(rand(2, 4))
                ->forVillage($village)
                ->service()
                ->create();
        }

        $this->command->info('Categories seeded successfully!');
        $this->command->info('Total categories created: ' . Category::count());
    }
}
