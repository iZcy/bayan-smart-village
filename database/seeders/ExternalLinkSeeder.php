<?php

namespace Database\Seeders;

use App\Models\ExternalLink;
use App\Models\SmeTourismPlace;
use Illuminate\Database\Seeder;

class ExternalLinkSeeder extends Seeder
{
    public function run(): void
    {
        $places = SmeTourismPlace::all();

        foreach ($places as $place) {
            // Each place gets 2-4 external links
            $linkCount = fake()->numberBetween(2, 4);

            // Create a mix of link types
            $linkTypes = ['instagram', 'whatsapp', 'facebook', 'website'];
            $selectedTypes = fake()->randomElements($linkTypes, $linkCount);

            foreach ($selectedTypes as $index => $type) {
                ExternalLink::factory()
                    ->$type()
                    ->create([
                        'place_id' => $place->id,
                        'sort_order' => $index,
                    ]);
            }
        }

        // Create some additional random links
        ExternalLink::factory()
            ->count(20)
            ->create();
    }
}
