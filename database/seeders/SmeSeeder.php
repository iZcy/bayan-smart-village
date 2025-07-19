<?php

namespace Database\Seeders;

use App\Models\Sme;
use App\Models\Community;
use App\Models\Place;
use Illuminate\Database\Seeder;

class SmeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding SMEs...');

        $communities = Community::all();

        if ($communities->isEmpty()) {
            $this->command->warn('No communities found. Please run CommunitySeeder first.');
            return;
        }

        $places = Place::all();

        foreach ($communities as $community) {
            $smeCount = rand(3, 8); // 3-8 SMEs per community

            $this->command->info("Creating {$smeCount} SMEs for {$community->name}");

            for ($i = 0; $i < $smeCount; $i++) {
                $smeFactory = Sme::factory()->forCommunity($community);

                // 40% chance to assign to a place in the same village
                if (rand(1, 100) <= 40) {
                    $villagePlaces = $places->where('village_id', $community->village_id);
                    if ($villagePlaces->isNotEmpty()) {
                        $smeFactory = $smeFactory->atPlace($villagePlaces->random());
                    }
                }

                // Mix of product and service SMEs
                $smeFactory = rand(1, 2) === 1 ? $smeFactory->product() : $smeFactory->service();

                // 70% chance to be verified
                if (rand(1, 100) <= 70) {
                    $smeFactory = $smeFactory->verified();
                }

                $smeFactory->create();
            }
        }

        $this->command->info('SMEs seeded successfully!');
        $this->command->info('Total SMEs created: ' . Sme::count());
        $this->command->info('Verified SMEs: ' . Sme::where('is_verified', true)->count());
        $this->command->info('Product SMEs: ' . Sme::where('type', 'product')->count());
        $this->command->info('Service SMEs: ' . Sme::where('type', 'service')->count());
    }
}
