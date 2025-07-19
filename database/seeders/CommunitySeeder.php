<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Village;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding communities...');

        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        foreach ($villages as $village) {
            $communityCount = rand(2, 4); // 2-4 communities per village

            $this->command->info("Creating {$communityCount} communities for {$village->name}");

            Community::factory()
                ->count($communityCount)
                ->forVillage($village)
                ->active()
                ->create();
        }

        $this->command->info('Communities seeded successfully!');
        $this->command->info('Total communities created: ' . Community::count());
    }
}
