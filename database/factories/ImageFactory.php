<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\SmeTourismPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition(): array
    {
        $captions = [
            'Pemandangan yang menakjubkan',
            'Suasana sore hari yang menenangkan',
            'Aktivitas menarik untuk keluarga',
            'Spot foto terbaik di area ini',
            'Kuliner khas yang lezat',
            'Interior yang nyaman dan modern',
            'Fasilitas lengkap untuk pengunjung',
            'Keindahan alam yang masih alami',
            'Pengalaman yang tak terlupakan',
            'Tempat favorit wisatawan lokal',
            null,
            null // Some images without captions
        ];

        return [
            'place_id' => SmeTourismPlace::factory(),
            'image_url' => 'https://picsum.photos/800/600?random=' . $this->faker->numberBetween(1, 1000),
            'caption' => $this->faker->randomElement($captions),
        ];
    }

    public function withCaption(): static
    {
        return $this->state(fn(array $attributes) => [
            'caption' => $this->faker->randomElement([
                'Pemandangan yang menakjubkan',
                'Suasana sore hari yang menenangkan',
                'Aktivitas menarik untuk keluarga',
                'Spot foto terbaik di area ini',
            ]),
        ]);
    }

    public function withoutCaption(): static
    {
        return $this->state(fn(array $attributes) => [
            'caption' => null,
        ]);
    }
}
