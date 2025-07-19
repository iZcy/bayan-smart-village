<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Sme;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        $productNames = [
            'Tas Anyaman Pandan Premium',
            'Batik Tulis Motif Klasik',
            'Keramik Hias Bunga',
            'Madu Asli Hutan',
            'Kopi Robusta Sangrai',
            'Dodol Durian Manis',
            'Keripik Pisang Renyah',
            'Tenun Ikat Tradisional',
            'Ukiran Kayu Jati',
            'Emping Melinjo Original',
            'Gula Aren Murni',
            'Kerajinan Bambu Unik',
            'Songket Palembang Asli',
            'Anyaman Rotan Cantik',
            'Selendang Sutra'
        ];

        $serviceNames = [
            'Paket Wisata Desa 1 Hari',
            'Jasa Pemandu Wisata Lokal',
            'Homestay Nyaman Keluarga',
            'Pijat Tradisional Relaksasi',
            'Kursus Membatik Pemula',
            'Catering Nasi Kotak',
            'Jasa Foto Dokumentasi',
            'Pelatihan Kerajinan Tangan',
            'Sewa Motor Harian',
            'Ojek Wisata Antar Jemput',
            'Jasa Laundry Kilat',
            'Warung Makan Gudeg'
        ];

        $type = $this->faker->randomElement(['product', 'service']);
        $names = $type === 'product' ? $productNames : $serviceNames;
        $name = $this->faker->randomElement($names);

        $price = $this->faker->optional(0.8)->randomFloat(0, 5000, 500000);
        $priceUnit = $price ? $this->faker->randomElement(['per item', 'per kg', 'per hari', 'per paket', 'per jam']) : null;

        return [
            'sme_id' => Sme::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(12),
            'price' => $price,
            'price_unit' => $priceUnit,
            'price_range_min' => $this->faker->optional(0.3)->randomFloat(0, 5000, 50000),
            'price_range_max' => $this->faker->optional(0.3)->randomFloat(0, 50000, 200000),
            'availability' => $this->faker->randomElement(['available', 'out_of_stock', 'seasonal', 'on_demand']),
            'seasonal_availability' => $this->faker->optional(0.3)->randomElements([
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ], $this->faker->numberBetween(2, 6)),
            'primary_image_url' => $this->faker->optional(0.8)->imageUrl(600, 400, 'business', true, 'product'),
            'materials' => $this->faker->optional(0.6)->randomElements([
                'Bambu',
                'Kayu Jati',
                'Rotan',
                'Pandan',
                'Kain Katun',
                'Sutra',
                'Keramik',
                'Tanah Liat',
                'Besi',
                'Kuningan',
                'Perak',
                'Emas'
            ], $this->faker->numberBetween(1, 3)),
            'colors' => $this->faker->optional(0.7)->randomElements([
                'Merah',
                'Biru',
                'Hijau',
                'Kuning',
                'Ungu',
                'Oranye',
                'Pink',
                'Coklat',
                'Hitam',
                'Putih',
                'Abu-abu',
                'Natural'
            ], $this->faker->numberBetween(1, 4)),
            'sizes' => $this->faker->optional(0.5)->randomElements([
                'XS',
                'S',
                'M',
                'L',
                'XL',
                'XXL',
                'Kecil',
                'Sedang',
                'Besar'
            ], $this->faker->numberBetween(1, 3)),
            'features' => $this->faker->optional(0.7)->randomElements([
                'Handmade',
                'Ramah Lingkungan',
                'Tahan Lama',
                'Mudah Dibersihkan',
                'Anti Air',
                'Ringan',
                'Fleksibel',
                'Unik',
                'Limited Edition'
            ], $this->faker->numberBetween(1, 4)),
            'certification' => $this->faker->optional(0.4)->randomElements([
                'Halal MUI',
                'BPOM',
                'SNI',
                'Organic',
                'Fair Trade',
                'ISO 9001'
            ], $this->faker->numberBetween(1, 2)),
            'production_time' => $this->faker->optional(0.6)->randomElement([
                '1-2 hari',
                '3-5 hari',
                '1 minggu',
                '2 minggu',
                '1 bulan',
                'Sesuai pesanan'
            ]),
            'minimum_order' => $this->faker->optional(0.4)->numberBetween(1, 10),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
            'view_count' => $this->faker->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the offer belongs to a specific SME.
     */
    public function forSme(Sme $sme): static
    {
        return $this->state(fn(array $attributes) => [
            'sme_id' => $sme->id,
        ]);
    }

    /**
     * Indicate that the offer belongs to a specific category.
     */
    public function inCategory(Category $category): static
    {
        return $this->state(fn(array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Product offer.
     */
    public function product(): static
    {
        $productNames = [
            'Tas Anyaman Pandan Premium',
            'Batik Tulis Motif Klasik',
            'Keramik Hias Bunga',
            'Madu Asli Hutan',
            'Kopi Robusta Sangrai',
            'Dodol Durian Manis',
            'Keripik Pisang Renyah',
            'Tenun Ikat Tradisional',
            'Ukiran Kayu Jati'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($productNames),
        ]);
    }

    /**
     * Service offer.
     */
    public function service(): static
    {
        $serviceNames = [
            'Paket Wisata Desa 1 Hari',
            'Jasa Pemandu Wisata Lokal',
            'Homestay Nyaman Keluarga',
            'Pijat Tradisional Relaksasi',
            'Kursus Membatik Pemula',
            'Catering Nasi Kotak'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($serviceNames),
        ]);
    }

    /**
     * Featured offer.
     */
    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Available offer.
     */
    public function available(): static
    {
        return $this->state(fn(array $attributes) => [
            'availability' => 'available',
        ]);
    }

    /**
     * Out of stock offer.
     */
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'availability' => 'out_of_stock',
        ]);
    }

    /**
     * Seasonal offer.
     */
    public function seasonal(): static
    {
        return $this->state(fn(array $attributes) => [
            'availability' => 'seasonal',
            'seasonal_availability' => $this->faker->randomElements([
                'June',
                'July',
                'August',
                'September'
            ], 3),
        ]);
    }

    /**
     * Popular offer with high view count.
     */
    public function popular(): static
    {
        return $this->state(fn(array $attributes) => [
            'view_count' => $this->faker->numberBetween(500, 5000),
        ]);
    }
}
