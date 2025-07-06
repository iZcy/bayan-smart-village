<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Predefined SME categories
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

        // Predefined Tourism categories
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

        // Create SME categories
        foreach ($smeCategories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'sme',
            ]);
        }

        // Create Tourism categories
        foreach ($tourismCategories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'tourism',
            ]);
        }
    }
}
