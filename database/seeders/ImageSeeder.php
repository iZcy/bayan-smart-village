<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\SmeTourismPlace;
use Illuminate\Database\Seeder;

class ImageSeeder extends Seeder
{
    public function run(): void
    {
        $places = SmeTourismPlace::all();

        // Create 2-5 images for each place
        foreach ($places as $place) {
            $imageCount = fake()->numberBetween(2, 5);

            Image::factory()
                ->count($imageCount)
                ->create([
                    'place_id' => $place->id,
                ]);
        }

        // Create some additional images with specific captions
        Image::factory()
            ->withCaption()
            ->count(10)
            ->create();
    }
}
