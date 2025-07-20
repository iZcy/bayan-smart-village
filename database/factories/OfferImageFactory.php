<?php

namespace Database\Factories;

use App\Models\OfferImage;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfferImage>
 */
class OfferImageFactory extends Factory
{
    protected $model = OfferImage::class;

    public function definition(): array
    {
        $descriptions = [
            'Tampak Depan Produk',
            'Tampak Samping Produk',
            'Detail Tekstur',
            'Tampak Belakang',
            'Close-up Detail',
            'Produk dalam Kemasan',
            'Produk Sedang Digunakan',
            'Variasi Warna',
            'Ukuran Produk',
            'Proses Pembuatan',
            'Bahan Baku',
            'Tahap Pengerjaan',
            'Alat yang Digunakan',
            'Finishing Process',
            'Quality Control',
            'Produk dalam Penggunaan',
            'Lifestyle Shot',
            'Dekorasi Rumah',
            'Fashion Style',
            'Outdoor Usage',
            'Indoor Setting'
        ];

        $altText = $this->faker->randomElement($descriptions);

        return [
            'offer_id' => Offer::factory(),
            'image_url' => $this->faker->imageUrl(600, 400),
            'alt_text' => $altText,
            'sort_order' => $this->faker->numberBetween(0, 20),
            'is_primary' => false, // Will be set to true for one image per offer in seeder
        ];
    }

    /**
     * Indicate that the image belongs to a specific offer.
     */
    public function forOffer(Offer $offer): static
    {
        return $this->state(fn(array $attributes) => [
            'offer_id' => $offer->id,
        ]);
    }

    /**
     * Primary image (main product image).
     */
    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => true,
            'sort_order' => 0,
            'alt_text' => 'Gambar Utama Produk',
            'image_url' => $this->faker->imageUrl(800, 600),
        ]);
    }

    /**
     * Secondary image (additional product images).
     */
    public function secondary(int $sortOrder = 0): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => false,
            'sort_order' => $sortOrder ?? $this->faker->numberBetween(1, 10),
            'alt_text' => $this->faker->randomElement([
                'Tampak Samping Produk',
                'Detail Produk',
                'Variasi Produk',
                'Produk dalam Kemasan',
                'Close-up Detail'
            ]),
        ]);
    }

    /**
     * Product detail image.
     */
    public function detail(): static
    {
        return $this->state(fn(array $attributes) => [
            'alt_text' => $this->faker->randomElement([
                'Detail Tekstur Produk',
                'Close-up Material',
                'Finishing Detail',
                'Jahitan Detail',
                'Motif Detail',
                'Kualitas Produk'
            ]),
            'image_url' => $this->faker->imageUrl(600, 400),
        ]);
    }

    /**
     * Process image (showing how product is made).
     */
    public function process(): static
    {
        return $this->state(fn(array $attributes) => [
            'alt_text' => $this->faker->randomElement([
                'Proses Pembuatan',
                'Tahap Pengerjaan',
                'Bahan Baku',
                'Alat Produksi',
                'Proses Finishing',
                'Quality Control'
            ]),
            'image_url' => $this->faker->imageUrl(600, 400),
        ]);
    }

    /**
     * Lifestyle image (product in use).
     */
    public function lifestyle(): static
    {
        return $this->state(fn(array $attributes) => [
            'alt_text' => $this->faker->randomElement([
                'Produk Sedang Digunakan',
                'Lifestyle dengan Produk',
                'Produk dalam Kehidupan',
                'Penggunaan Sehari-hari',
                'Produk dalam Dekorasi',
                'Gaya Hidup'
            ]),
            'image_url' => $this->faker->imageUrl(600, 400),
        ]);
    }

    /**
     * Packaging image.
     */
    public function packaging(): static
    {
        return $this->state(fn(array $attributes) => [
            'alt_text' => $this->faker->randomElement([
                'Kemasan Produk',
                'Packaging Design',
                'Produk dalam Kemasan',
                'Box Packaging',
                'Wrapping',
                'Gift Packaging'
            ]),
            'image_url' => $this->faker->imageUrl(600, 400),
        ]);
    }

    /**
     * Comparison image (size reference, color variations, etc.).
     */
    public function comparison(): static
    {
        return $this->state(fn(array $attributes) => [
            'alt_text' => $this->faker->randomElement([
                'Perbandingan Ukuran',
                'Variasi Warna',
                'Pilihan Model',
                'Size Comparison',
                'Color Options',
                'Model Variations'
            ]),
            'image_url' => $this->faker->imageUrl(600, 400),
        ]);
    }

    /**
     * Gallery image with specific order.
     */
    public function gallery(int $order): static
    {
        return $this->state(fn(array $attributes) => [
            'sort_order' => $order,
            'is_primary' => $order === 0,
        ]);
    }
}
