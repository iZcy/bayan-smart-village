<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Village;
use App\Models\SmeTourismPlace;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $productNames = [
            // Food & Beverages
            'Kopi Robusta Lombok',
            'Madu Hutan Rinjani',
            'Sambal Plecing Khas',
            'Kerupuk Ikan Tradisional',
            'Abon Ikan Tongkol',
            'Keripik Singkong Pedas',
            'Teh Herbal Daun Kumis Kucing',
            'Sirup Markisa Organik',

            // Handicrafts
            'Tas Anyaman Pandan',
            'Kain Tenun Sasak',
            'Kerajinan Bambu Unik',
            'Ukiran Kayu Jati',
            'Gerabah Banyumulek',
            'Perhiasan Mutiara Lombok',
            'Sandal Anyaman Tradisional',
            'Topi Caping Sasak',

            // Tourism Services
            'Paket Wisata Gili 3 Hari',
            'Tour Guide Rinjani',
            'Homestay Desa Sasak',
            'Workshop Tenun Traditional',
            'Kelas Memasak Plecing',
            'Rental Sepeda Desa',

            // Health & Beauty
            'Sabun Herbal Alami',
            'Minyak Kelapa Virgin',
            'Lulur Tradisional Sasak',
            'Jamu Herbal Sehat',
        ];

        $availability = $this->faker->randomElement(['available', 'available', 'available', 'seasonal', 'on_demand']);
        $hasFixedPrice = $this->faker->boolean(70);

        return [
            'village_id' => $this->faker->boolean(80) ? Village::active()->inRandomOrder()->first()?->id : null,
            'place_id' => $this->faker->boolean(70) ? SmeTourismPlace::inRandomOrder()->first()?->id : null,
            'category_id' => Category::inRandomOrder()->first()->id,
            'name' => $this->faker->randomElement($productNames),
            'description' => $this->generateRichDescription(),
            'short_description' => $this->faker->text(400),
            'price' => $hasFixedPrice ? $this->faker->numberBetween(10000, 500000) : null,
            'price_unit' => $this->faker->randomElement(['per piece', 'per kg', 'per meter', 'per pack', 'per bottle', 'per set']),
            'price_range_min' => !$hasFixedPrice ? $this->faker->numberBetween(5000, 100000) : null,
            'price_range_max' => !$hasFixedPrice ? $this->faker->numberBetween(150000, 800000) : null,
            'availability' => $availability,
            'seasonal_availability' => $availability === 'seasonal' ? $this->generateSeasonalAvailability() : null,
            'primary_image_url' => 'https://picsum.photos/600/400?random=' . $this->faker->numberBetween(1, 1000),
            'materials' => $this->generateMaterials(),
            'colors' => $this->generateColors(),
            'sizes' => $this->generateSizes(),
            'features' => $this->generateFeatures(),
            'certification' => $this->faker->boolean(30) ? $this->generateCertifications() : null,
            'production_time' => $this->faker->randomElement(['1-2 hari', '3-5 hari', '1 minggu', '2 minggu', 'Siap stock']),
            'minimum_order' => $this->faker->boolean(40) ? $this->faker->numberBetween(1, 10) : null,
            'is_featured' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(95),
            'view_count' => $this->faker->numberBetween(0, 500),
        ];
    }

    private function generateRichDescription(): string
    {
        $paragraphs = [
            '<p>Produk berkualitas tinggi yang dibuat dengan teknik tradisional turun temurun. Setiap item diproduksi dengan penuh perhatian terhadap detail dan menggunakan bahan-bahan terbaik dari alam Lombok.</p>',

            '<p>Dibuat oleh pengrajin lokal yang berpengalaman puluhan tahun dalam bidangnya. Proses pembuatan dilakukan secara manual dengan mempertahankan keaslian dan kualitas produk tradisional.</p>',

            '<h3>Keunggulan Produk</h3>
            <ul>
                <li>100% handmade dengan kualitas premium</li>
                <li>Menggunakan bahan baku lokal pilihan</li>
                <li>Ramah lingkungan dan sustainable</li>
                <li>Mendukung ekonomi masyarakat lokal</li>
            </ul>',

            '<p>Cocok untuk oleh-oleh, koleksi pribadi, atau sebagai hadiah istimewa. Produk ini tidak hanya memiliki nilai estetika tinggi tetapi juga nilai budaya yang mendalam.</p>',

            '<h3>Cara Perawatan</h3>
            <p>Untuk menjaga kualitas dan daya tahan produk, disarankan untuk menyimpan di tempat yang kering dan terhindar dari sinar matahari langsung. Bersihkan secara berkala dengan kain lembut.</p>',
        ];

        $selectedParagraphs = $this->faker->randomElements($paragraphs, $this->faker->numberBetween(3, 5));
        return implode('', $selectedParagraphs);
    }

    private function generateSeasonalAvailability(): array
    {
        return $this->faker->randomElements([
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
        ], $this->faker->numberBetween(3, 8));
    }

    private function generateMaterials(): array
    {
        $materials = [
            'Bamboo',
            'Kayu Jati',
            'Pandan',
            'Rotan',
            'Kelapa',
            'Cotton',
            'Sutra',
            'Benang Emas',
            'Batu Alam',
            'Mutiara',
            'Tanah Liat',
            'Daun Lontar',
            'Serat Alami',
            'Kain Tradisional'
        ];

        return $this->faker->randomElements($materials, $this->faker->numberBetween(1, 4));
    }

    private function generateColors(): array
    {
        $colors = [
            'Natural',
            'Coklat',
            'Hitam',
            'Putih',
            'Merah',
            'Biru',
            'Hijau',
            'Kuning',
            'Orange',
            'Ungu',
            'Pink',
            'Gold',
            'Silver',
            'Cream',
            'Maroon'
        ];

        return $this->faker->randomElements($colors, $this->faker->numberBetween(1, 5));
    }

    private function generateSizes(): array
    {
        $sizeTypes = [
            ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            ['25cm', '30cm', '35cm', '40cm', '50cm'],
            ['Small', 'Medium', 'Large'],
            ['100gr', '250gr', '500gr', '1kg'],
            ['Mini', 'Standard', 'Jumbo']
        ];

        $selectedType = $this->faker->randomElement($sizeTypes);
        return $this->faker->randomElements($selectedType, $this->faker->numberBetween(2, 4));
    }

    private function generateFeatures(): array
    {
        $features = [
            'Handmade',
            'Eco-friendly',
            'Tahan lama',
            'Ringan',
            'Mudah dibersihkan',
            'Anti air',
            'Breathable',
            'Hypoallergenic',
            'Organik',
            'Fair trade',
            'Unik',
            'Limited edition',
            'Customizable',
            'Portable',
            'Multifungsi'
        ];

        return $this->faker->randomElements($features, $this->faker->numberBetween(2, 6));
    }

    private function generateCertifications(): array
    {
        $certifications = [
            'Halal',
            'Organik',
            'Fair Trade',
            'Eco-friendly',
            'BPOM',
            'SNI',
            'ISO 9001',
            'Handmade Certificate'
        ];

        return $this->faker->randomElements($certifications, $this->faker->numberBetween(1, 3));
    }

    public function withVillage(Village $village): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => $village->id,
        ]);
    }

    public function withPlace(SmeTourismPlace $place): static
    {
        return $this->state(fn(array $attributes) => [
            'place_id' => $place->id,
            'village_id' => $place->village_id,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function seasonal(): static
    {
        return $this->state(fn(array $attributes) => [
            'availability' => 'seasonal',
            'seasonal_availability' => $this->generateSeasonalAvailability(),
        ]);
    }

    public function withFixedPrice(): static
    {
        return $this->state(fn(array $attributes) => [
            'price' => $this->faker->numberBetween(10000, 500000),
            'price_range_min' => null,
            'price_range_max' => null,
        ]);
    }

    public function withPriceRange(): static
    {
        $min = $this->faker->numberBetween(5000, 100000);
        $max = $this->faker->numberBetween($min + 10000, 800000);

        return $this->state(fn(array $attributes) => [
            'price' => null,
            'price_range_min' => $min,
            'price_range_max' => $max,
        ]);
    }
}

// ProductEcommerceLink Factory
class ProductEcommerceLinkFactory extends Factory
{
    protected $model = \App\Models\ProductEcommerceLink::class;

    public function definition(): array
    {
        $platforms = [
            'tokopedia' => [
                'stores' => ['Toko Lombok Heritage', 'Sasak Craft Store', 'Rinjani Products'],
                'base_urls' => ['https://tokopedia.com/']
            ],
            'shopee' => [
                'stores' => ['Lombok Traditional', 'Heritage Craft Shop', 'Sasak Authentic'],
                'base_urls' => ['https://shopee.co.id/']
            ],
            'instagram' => [
                'stores' => ['@lombok_craft', '@sasak_heritage', '@rinjani_products'],
                'base_urls' => ['https://instagram.com/']
            ],
            'whatsapp' => [
                'stores' => ['WhatsApp Business', 'Direct Order', 'Chat Penjual'],
                'base_urls' => ['https://wa.me/62812', 'https://wa.me/62813', 'https://wa.me/62815']
            ],
            'website' => [
                'stores' => ['Official Website', 'Toko Online', 'Website Resmi'],
                'base_urls' => ['https://www.', 'https://']
            ]
        ];

        $platform = $this->faker->randomElement(array_keys($platforms));
        $platformData = $platforms[$platform];

        return [
            'platform' => $platform,
            'store_name' => $this->faker->randomElement($platformData['stores']),
            'product_url' => $this->generateProductUrl($platform, $platformData['base_urls']),
            'price_on_platform' => $this->faker->boolean(70) ? $this->faker->numberBetween(15000, 600000) : null,
            'is_verified' => $this->faker->boolean(60),
            'is_active' => $this->faker->boolean(90),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'click_count' => $this->faker->numberBetween(0, 200),
            'last_verified_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
        ];
    }

    private function generateProductUrl(string $platform, array $baseUrls): string
    {
        $baseUrl = $this->faker->randomElement($baseUrls);

        return match ($platform) {
            'tokopedia' => $baseUrl . $this->faker->slug . '/' . $this->faker->slug,
            'shopee' => $baseUrl . $this->faker->slug . '-i.' . $this->faker->numberBetween(100000, 999999) . '.' . $this->faker->numberBetween(1000000, 9999999),
            'instagram' => $baseUrl . $this->faker->userName . '/p/' . $this->faker->regexify('[A-Za-z0-9_-]{11}'),
            'whatsapp' => $baseUrl . $this->faker->numberBetween(10000000, 99999999),
            'website' => $baseUrl . $this->faker->domainName . '/product/' . $this->faker->slug,
            default => $baseUrl . $this->faker->slug
        };
    }

    public function forProduct(\App\Models\Product $product): static
    {
        return $this->state(fn(array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function platform(string $platform): static
    {
        return $this->state(fn(array $attributes) => [
            'platform' => $platform,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_verified' => true,
            'last_verified_at' => now(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}

// ProductImage Factory
class ProductImageFactory extends Factory
{
    protected $model = \App\Models\ProductImage::class;

    public function definition(): array
    {
        return [
            'image_url' => 'https://picsum.photos/600/400?random=' . $this->faker->numberBetween(1, 1000),
            'alt_text' => $this->faker->sentence(4),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_primary' => false,
        ];
    }

    public function forProduct(\App\Models\Product $product): static
    {
        return $this->state(fn(array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }
}

// ProductTag Factory
class ProductTagFactory extends Factory
{
    protected $model = \App\Models\ProductTag::class;

    public function definition(): array
    {
        $tags = [
            'handmade',
            'traditional',
            'organic',
            'eco-friendly',
            'sustainable',
            'artisan',
            'heritage',
            'authentic',
            'premium',
            'limited-edition',
            'cultural',
            'vintage',
            'modern',
            'minimalist',
            'rustic',
            'luxury',
            'affordable',
            'gift',
            'souvenir',
            'collectible'
        ];

        $name = $this->faker->unique()->randomElement($tags);

        return [
            'name' => ucfirst($name),
            'usage_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}
