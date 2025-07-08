<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductEcommerceLink;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\Village;
use App\Models\SmeTourismPlace;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating product tags...');
        $this->createProductTags();

        $this->command->info('Creating featured products...');
        $this->createFeaturedProducts();

        $this->command->info('Creating random products...');
        $this->createRandomProducts();

        $this->command->info('Adding e-commerce links to products...');
        $this->addEcommerceLinks();

        $this->command->info('Adding additional images to products...');
        $this->addProductImages();

        $this->command->info('Assigning tags to products...');
        $this->assignTagsToProducts();

        $totalProducts = Product::count();
        $featuredProducts = Product::where('is_featured', true)->count();
        $productsWithLinks = Product::whereHas('ecommerceLinks')->count();

        $this->command->info("✅ Product seeding completed!");
        $this->command->info("   Total products: {$totalProducts}");
        $this->command->info("   Featured products: {$featuredProducts}");
        $this->command->info("   Products with e-commerce links: {$productsWithLinks}");
    }

    private function createProductTags(): void
    {
        $predefinedTags = [
            'Handmade',
            'Traditional',
            'Organic',
            'Eco-friendly',
            'Sustainable',
            'Artisan',
            'Heritage',
            'Authentic',
            'Premium',
            'Limited Edition',
            'Cultural',
            'Vintage',
            'Modern',
            'Minimalist',
            'Rustic',
            'Luxury',
            'Affordable',
            'Gift',
            'Souvenir',
            'Collectible',
            'Natural',
            'Sasak',
            'Lombok',
            'Indonesia',
            'Tropical'
        ];

        foreach ($predefinedTags as $tagName) {
            ProductTag::firstOrCreate(
                ['name' => $tagName],
                ['usage_count' => 0]
            );
        }
    }

    private function createFeaturedProducts(): void
    {
        $villages = Village::active()->get();
        $categories = Category::all();

        $featuredProducts = [
            [
                'name' => 'Kain Tenun Sasak Premium',
                'short_description' => 'Kain tenun tradisional Sasak dengan motif songket emas, dibuat oleh pengrajin berpengalaman puluhan tahun.',
                'description' => $this->getFeaturedDescription('tenun'),
                'price' => 750000,
                'price_unit' => 'per meter',
                'category_name' => 'Toko Pakaian',
                'materials' => ['Benang Sutra', 'Benang Emas', 'Cotton'],
                'colors' => ['Merah', 'Gold', 'Hijau'],
                'features' => ['Handmade', 'Limited Edition', 'Heritage', 'Premium Quality'],
                'certification' => ['UNESCO Heritage', 'Handmade Certificate'],
            ],
            [
                'name' => 'Madu Hutan Rinjani Organik',
                'short_description' => 'Madu murni dari hutan lereng Gunung Rinjani, dipanen secara tradisional dan sustainable.',
                'description' => $this->getFeaturedDescription('madu'),
                'price' => 125000,
                'price_unit' => 'per botol 500ml',
                'category_name' => 'Warung Makan',
                'materials' => ['Madu Murni', 'Botol Kaca'],
                'colors' => ['Golden'],
                'features' => ['Organic', 'Wild Honey', 'Sustainable', 'Traditional Harvest'],
                'certification' => ['Organic', 'BPOM', 'Halal'],
            ],
            [
                'name' => 'Tas Anyaman Pandan Lombok',
                'short_description' => 'Tas anyaman dari daun pandan pilihan dengan desain modern namun tetap mempertahankan teknik tradisional.',
                'description' => $this->getFeaturedDescription('tas'),
                'price_range_min' => 85000,
                'price_range_max' => 250000,
                'price_unit' => 'per piece',
                'category_name' => 'Kerajinan Tangan',
                'materials' => ['Daun Pandan', 'Benang Katun'],
                'colors' => ['Natural', 'Coklat', 'Hitam'],
                'sizes' => ['Small', 'Medium', 'Large'],
                'features' => ['Eco-friendly', 'Lightweight', 'Durable', 'Handwoven'],
            ],
            [
                'name' => 'Paket Wisata Gili 3 Hari 2 Malam',
                'short_description' => 'Paket wisata lengkap explore 3 Gili dengan snorkeling, sunset trip, dan pengalaman budaya lokal.',
                'description' => $this->getFeaturedDescription('wisata'),
                'price' => 1250000,
                'price_unit' => 'per person',
                'category_name' => 'Desa Wisata',
                'features' => ['All Inclusive', 'Local Guide', 'Snorkeling Equipment', 'Traditional Boat'],
                'certification' => ['Licensed Tour Operator', 'Eco Tourism'],
            ],
        ];

        foreach ($featuredProducts as $productData) {
            $category = Category::where('name', $productData['category_name'])->first();
            $village = $villages->random();
            $place = $village->places()->where('category_id', $category->id)->first();

            $product = Product::create([
                'village_id' => $village->id,
                'place_id' => $place?->id,
                'category_id' => $category->id,
                'name' => $productData['name'],
                'short_description' => $productData['short_description'],
                'description' => $productData['description'],
                'price' => $productData['price'] ?? null,
                'price_range_min' => $productData['price_range_min'] ?? null,
                'price_range_max' => $productData['price_range_max'] ?? null,
                'price_unit' => $productData['price_unit'],
                'availability' => 'available',
                'primary_image_url' => 'https://picsum.photos/600/400?random=' . fake()->numberBetween(1, 100),
                'materials' => $productData['materials'] ?? null,
                'colors' => $productData['colors'] ?? null,
                'sizes' => $productData['sizes'] ?? null,
                'features' => $productData['features'] ?? null,
                'certification' => $productData['certification'] ?? null,
                'production_time' => fake()->randomElement(['2-3 hari', '1 minggu', 'Ready stock']),
                'is_featured' => true,
                'is_active' => true,
                'view_count' => fake()->numberBetween(50, 300),
            ]);

            $this->command->info("  ✓ Created featured product: {$product->name}");
        }
    }

    private function createRandomProducts(): void
    {
        $villages = Village::active()->get();
        $places = SmeTourismPlace::all();

        // Create 40 random products
        for ($i = 0; $i < 40; $i++) {
            $village = $villages->random();
            $place = $places->where('village_id', $village->id)->random();

            Product::factory()
                ->withVillage($village)
                ->withPlace($place)
                ->create();
        }

        // Create some products without places (village-level products)
        for ($i = 0; $i < 10; $i++) {
            $village = $villages->random();

            Product::factory()
                ->withVillage($village)
                ->create([
                    'place_id' => null,
                ]);
        }

        // Create some products with different characteristics
        Product::factory()->featured()->count(3)->create();
        Product::factory()->seasonal()->count(5)->create();
        Product::factory()->withPriceRange()->count(8)->create();
    }

    private function addEcommerceLinks(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            // 80% chance of having e-commerce links
            if (fake()->boolean(80)) {
                $linkCount = fake()->numberBetween(1, 4);

                for ($i = 0; $i < $linkCount; $i++) {
                    ProductEcommerceLink::factory()
                        ->forProduct($product)
                        ->create();
                }
            }
        }

        // Ensure some products have verified links
        Product::inRandomOrder()
            ->take(15)
            ->get()
            ->each(function ($product) {
                $product->ecommerceLinks()
                    ->inRandomOrder()
                    ->first()
                    ?->markAsVerified();
            });
    }

    private function addProductImages(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            // 60% chance of having additional images
            if (fake()->boolean(60)) {
                $imageCount = fake()->numberBetween(2, 5);

                for ($i = 0; $i < $imageCount; $i++) {
                    ProductImage::factory()
                        ->forProduct($product)
                        ->create([
                            'sort_order' => $i + 1,
                        ]);
                }
            }
        }
    }

    private function assignTagsToProducts(): void
    {
        $products = Product::all();
        $tags = ProductTag::all();

        foreach ($products as $product) {
            // Assign 2-5 random tags to each product
            $productTags = $tags->random(fake()->numberBetween(2, 5));
            $product->tags()->attach($productTags->pluck('id'));

            // Update tag usage counts
            foreach ($productTags as $tag) {
                $tag->incrementUsage();
            }
        }
    }

    private function getFeaturedDescription(string $type): string
    {
        $descriptions = [
            'tenun' => '<h2>Kain Tenun Sasak Berkualitas Premium</h2>
                <p>Kain tenun tradisional Sasak ini merupakan karya seni yang diwariskan turun temurun oleh para pengrajin Lombok. Setiap helai benang ditenun dengan penuh kesabaran dan keahlian tinggi, menghasilkan motif songket yang indah dan elegan.</p>

                <h3>Keunggulan Produk</h3>
                <ul>
                    <li>Dibuat dengan teknik tenun tradisional ATBM (Alat Tenun Bukan Mesin)</li>
                    <li>Menggunakan benang sutra berkualitas tinggi</li>
                    <li>Motif songket emas yang mewah dan tahan lama</li>
                    <li>Proses pembuatan memakan waktu 2-3 bulan per meter</li>
                    <li>Telah mendapat pengakuan UNESCO sebagai warisan budaya</li>
                </ul>

                <p>Cocok untuk berbagai acara formal, pernikahan adat, atau sebagai koleksi kain tradisional Indonesia. Investasi budaya yang bernilai tinggi dan akan semakin langka seiring berjalannya waktu.</p>',

            'madu' => '<h2>Madu Hutan Rinjani - Kemurnian dari Alam</h2>
                <p>Madu hutan yang dipanen langsung dari sarang lebah liar di lereng Gunung Rinjani. Diproses secara traditional tanpa pemanasan berlebih untuk mempertahankan khasiat alami dan enzim yang bermanfaat bagi kesehatan.</p>

                <h3>Manfaat Kesehatan</h3>
                <ul>
                    <li>Meningkatkan sistem imunitas tubuh</li>
                    <li>Mengandung antioksidan tinggi</li>
                    <li>Membantu proses penyembuhan luka</li>
                    <li>Sumber energi alami yang sehat</li>
                    <li>Membantu mengatasi masalah pencernaan</li>
                </ul>

                <p>Dipanen dengan metode sustainable yang tidak merusak habitat lebah dan menjaga kelestarian hutan Rinjani. Setiap botol mewakili komitmen terhadap konservasi alam dan pemberdayaan masyarakat lokal.</p>',

            'tas' => '<h2>Tas Anyaman Pandan - Tradisi Bertemu Modernitas</h2>
                <p>Tas anyaman yang memadukan kearifan lokal dengan desain kontemporer. Dibuat dari daun pandan pilihan yang diproses secara khusus untuk menghasilkan serat yang kuat dan tahan lama.</p>

                <h3>Proses Pembuatan</h3>
                <ul>
                    <li>Pemilihan daun pandan berkualitas tinggi</li>
                    <li>Proses pengeringan dan pewarnaan alami</li>
                    <li>Teknik anyaman tradisional yang telah disempurnakan</li>
                    <li>Finishing dengan detail modern</li>
                    <li>Quality control untuk memastikan durabilitas</li>
                </ul>

                <p>Ramah lingkungan, ringan namun kuat, dan memiliki karakteristik unik yang membuat setiap tas berbeda. Investasi fashion yang sustainable dan mendukung ekonomi kreatif lokal.</p>',

            'wisata' => '<h2>Eksplorasi Gili Trawangan, Meno & Air</h2>
                <p>Nikmati pengalaman wisata tak terlupakan menjelajahi tiga pulau eksotis di utara Lombok. Paket wisata yang dirancang khusus untuk memberikan pengalaman komprehensif budaya, alam, dan petualangan.</p>

                <h3>Itinerary Highlights</h3>
                <ul>
                    <li><strong>Hari 1:</strong> Arrival, island hopping, snorkeling di spot terbaik</li>
                    <li><strong>Hari 2:</strong> Sunrise diving, cultural village tour, sunset dinner</li>
                    <li><strong>Hari 3:</strong> Freediving lesson, souvenir shopping, departure</li>
                </ul>

                <h3>Fasilitas Termasuk</h3>
                <ul>
                    <li>Akomodasi homestay autentik</li>
                    <li>Transportasi perahu tradisional cidomo</li>
                    <li>Peralatan snorkeling dan diving</li>
                    <li>Makan 3x sehari dengan menu lokal</li>
                    <li>Guide lokal berpengalaman</li>
                    <li>Dokumentasi foto underwater</li>
                </ul>

                <p>Tour yang sustainable dengan fokus pada konservasi laut dan pemberdayaan masyarakat lokal. Kontribusi langsung untuk pelestarian ekosistem laut dan budaya Sasak.</p>',
        ];

        return $descriptions[$type] ?? '<p>Produk berkualitas tinggi dari Lombok.</p>';
    }
}
