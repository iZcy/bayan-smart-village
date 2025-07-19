<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $productCategories = [
            'Kerajinan Tangan' => 'heroicon-o-cube',
            'Makanan & Minuman' => 'heroicon-o-cake',
            'Tekstil & Pakaian' => 'heroicon-o-scissors',
            'Perhiasan' => 'heroicon-o-gem',
            'Furniture' => 'heroicon-o-home',
            'Keramik & Gerabah' => 'heroicon-o-beaker',
            'Produk Pertanian' => 'heroicon-o-leaf',
            'Produk Perikanan' => 'heroicon-o-fish',
            'Olahan Susu' => 'heroicon-o-milk',
            'Rempah-rempah' => 'heroicon-o-fire',
        ];

        $serviceCategories = [
            'Jasa Pemandu Wisata' => 'heroicon-o-map',
            'Transportasi' => 'heroicon-o-truck',
            'Penginapan' => 'heroicon-o-building-office',
            'Kuliner' => 'heroicon-o-utensils',
            'Jasa Fotografi' => 'heroicon-o-camera',
            'Spa & Wellness' => 'heroicon-o-heart',
            'Jasa Pertanian' => 'heroicon-o-wrench-screwdriver',
            'Jasa Konstruksi' => 'heroicon-o-hammer',
            'Pendidikan & Pelatihan' => 'heroicon-o-academic-cap',
            'Jasa Keuangan' => 'heroicon-o-banknotes',
        ];

        $type = $this->faker->randomElement(['product', 'service']);
        $categories = $type === 'product' ? $productCategories : $serviceCategories;
        $name = $this->faker->randomElement(array_keys($categories));
        $icon = $categories[$name];

        return [
            'village_id' => Village::factory(),
            'name' => $name,
            'type' => $type,
            'description' => $this->faker->sentence(),
            'icon' => $icon,
        ];
    }

    /**
     * Indicate that the category belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => $village->id,
        ]);
    }

    /**
     * Product category type.
     */
    public function product(): static
    {
        $productCategories = [
            'Kerajinan Tangan' => 'heroicon-o-cube',
            'Makanan & Minuman' => 'heroicon-o-cake',
            'Tekstil & Pakaian' => 'heroicon-o-scissors',
            'Perhiasan' => 'heroicon-o-gem',
            'Furniture' => 'heroicon-o-home',
            'Keramik & Gerabah' => 'heroicon-o-beaker',
            'Produk Pertanian' => 'heroicon-o-leaf',
            'Produk Perikanan' => 'heroicon-o-fish',
        ];

        $name = $this->faker->randomElement(array_keys($productCategories));
        $icon = $productCategories[$name];

        return $this->state(fn(array $attributes) => [
            'name' => $name,
            'type' => 'product',
            'icon' => $icon,
        ]);
    }

    /**
     * Service category type.
     */
    public function service(): static
    {
        $serviceCategories = [
            'Jasa Pemandu Wisata' => 'heroicon-o-map',
            'Transportasi' => 'heroicon-o-truck',
            'Penginapan' => 'heroicon-o-building-office',
            'Kuliner' => 'heroicon-o-utensils',
            'Jasa Fotografi' => 'heroicon-o-camera',
            'Spa & Wellness' => 'heroicon-o-heart',
            'Jasa Pertanian' => 'heroicon-o-wrench-screwdriver',
            'Pendidikan & Pelatihan' => 'heroicon-o-academic-cap',
        ];

        $name = $this->faker->randomElement(array_keys($serviceCategories));
        $icon = $serviceCategories[$name];

        return $this->state(fn(array $attributes) => [
            'name' => $name,
            'type' => 'service',
            'icon' => $icon,
        ]);
    }
}
