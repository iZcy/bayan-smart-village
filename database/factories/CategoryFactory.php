<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $smeCategories = [
            'Warung Makan',
            'Kedai Kopi',
            'Toko Kelontong',
            'Laundry',
            'Salon & Barbershop',
            'Bengkel Motor',
            'Toko Elektronik',
            'Toko Pakaian',
            'Apotek',
            'Toko Bangunan'
        ];

        $tourismCategories = [
            'Pantai',
            'Gunung',
            'Air Terjun',
            'Danau',
            'Gua',
            'Candi',
            'Museum',
            'Taman Nasional',
            'Desa Wisata',
            'Spot Foto',
            'Tempat Ibadah',
            'Pasar Tradisional'
        ];

        $type = $this->faker->randomElement(['sme', 'tourism']);

        if ($type === 'sme') {
            $name = $this->faker->randomElement($smeCategories);
        } else {
            $name = $this->faker->randomElement($tourismCategories);
        }

        return [
            'name' => $name,
            'type' => $type,
        ];
    }

    public function sme(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'sme',
        ]);
    }

    public function tourism(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'tourism',
        ]);
    }
}
