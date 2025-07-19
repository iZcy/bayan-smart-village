<?php

namespace Database\Factories;

use App\Models\OfferEcommerceLink;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfferEcommerceLink>
 */
class OfferEcommerceLinkFactory extends Factory
{
    protected $model = OfferEcommerceLink::class;

    public function definition(): array
    {
        $platforms = [
            'tokopedia' => [
                'url_pattern' => 'https://tokopedia.com/shop/{shop}/product/{product}',
                'store_examples' => ['TokoKrajinan', 'HandmadeIndonesia', 'UKMNusantara', 'KaryaLokal']
            ],
            'shopee' => [
                'url_pattern' => 'https://shopee.co.id/product/{shop}/{product}',
                'store_examples' => ['Shopee_Craft', 'IndonesiaHandmade', 'LocalProduct', 'KrajiBagus']
            ],
            'tiktok_shop' => [
                'url_pattern' => 'https://shop.tiktok.com/@{shop}/product/{product}',
                'store_examples' => ['tiktokkraji', 'handmadeid', 'lokalcraft', 'umkmnusantara']
            ],
            'bukalapak' => [
                'url_pattern' => 'https://bukalapak.com/p/{category}/{product}',
                'store_examples' => ['Bukalapak_Craft', 'HandmadeBL', 'KrayaLokal', 'UMKMBL']
            ],
            'blibli' => [
                'url_pattern' => 'https://blibli.com/p/{product}',
                'store_examples' => ['Blibli_Official', 'HandmadeBlibli', 'LocalCraft', 'IndonesiaProduct']
            ],
            'lazada' => [
                'url_pattern' => 'https://lazada.co.id/products/{product}',
                'store_examples' => ['Lazada_Craft', 'HandmadeLZ', 'LocalProductLZ', 'UMKMLZ']
            ],
            'instagram' => [
                'url_pattern' => 'https://instagram.com/p/{post}',
                'store_examples' => ['@kerajinan_lokal', '@handmade_id', '@umkm_nusantara', '@kriya_indonesia']
            ],
            'whatsapp' => [
                'url_pattern' => 'https://wa.me/{phone}?text=Halo,%20saya%20tertarik%20dengan%20produk%20{product}',
                'store_examples' => ['WhatsApp Business', 'Kontak Langsung', 'Chat Penjual', 'Order WhatsApp']
            ],
            'website' => [
                'url_pattern' => 'https://{domain}/product/{product}',
                'store_examples' => ['Official Website', 'Toko Online', 'Website Resmi', 'Portal Penjualan']
            ],
            'other' => [
                'url_pattern' => 'https://{platform}.com/product/{product}',
                'store_examples' => ['Platform Lain', 'Marketplace Lokal', 'Toko Online', 'E-commerce']
            ]
        ];

        $platform = $this->faker->randomKey($platforms);
        $platformData = $platforms[$platform];
        $storeName = $this->faker->randomElement($platformData['store_examples']);

        // Generate realistic product URL
        $productUrl = $this->generateProductUrl($platform, $platformData['url_pattern']);

        // Generate price with some variation from base price
        $basePrice = $this->faker->randomFloat(0, 10000, 500000);
        $priceVariation = $this->faker->randomFloat(0, 0.9, 1.1); // ±10% variation
        $priceOnPlatform = $basePrice * $priceVariation;

        return [
            'offer_id' => Offer::factory(),
            'platform' => $platform,
            'store_name' => $storeName,
            'product_url' => $productUrl,
            'price_on_platform' => $priceOnPlatform,
            'is_verified' => $this->faker->boolean(70), // 70% chance of being verified
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
            'sort_order' => $this->faker->numberBetween(0, 10),
            'click_count' => $this->faker->numberBetween(0, 200),
            'last_verified_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Generate realistic product URL based on platform.
     */
    private function generateProductUrl(string $platform, string $urlPattern): string
    {
        $replacements = [
            '{shop}' => $this->faker->userName(),
            '{product}' => $this->faker->slug(3),
            '{category}' => $this->faker->randomElement(['kerajinan', 'makanan', 'fashion', 'elektronik']),
            '{post}' => $this->faker->regexify('[A-Za-z0-9_-]{11}'),
            '{phone}' => $this->faker->numerify('62###########'),
            '{domain}' => $this->faker->domainWord(),
            '{platform}' => $this->faker->randomElement(['marketplace', 'ecommerce', 'shop']),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $urlPattern);
    }

    /**
     * Indicate that the link belongs to a specific offer.
     */
    public function forOffer(Offer $offer): static
    {
        return $this->state(fn(array $attributes) => [
            'offer_id' => $offer->id,
        ]);
    }

    /**
     * Tokopedia platform.
     */
    public function tokopedia(): static
    {
        return $this->state(fn(array $attributes) => [
            'platform' => 'tokopedia',
            'store_name' => $this->faker->randomElement(['TokoKrajinan', 'HandmadeIndonesia', 'UKMNusantara']),
            'product_url' => 'https://tokopedia.com/shop/' . $this->faker->userName() . '/product/' . $this->faker->slug(3),
        ]);
    }

    /**
     * Shopee platform.
     */
    public function shopee(): static
    {
        return $this->state(fn(array $attributes) => [
            'platform' => 'shopee',
            'store_name' => $this->faker->randomElement(['Shopee_Craft', 'IndonesiaHandmade', 'LocalProduct']),
            'product_url' => 'https://shopee.co.id/product/' . $this->faker->numerify('########') . '/' . $this->faker->numerify('#########'),
        ]);
    }

    /**
     * TikTok Shop platform.
     */
    public function tiktokShop(): static
    {
        return $this->state(fn(array $attributes) => [
            'platform' => 'tiktok_shop',
            'store_name' => $this->faker->randomElement(['tiktokkraji', 'handmadeid', 'lokalcraft']),
            'product_url' => 'https://shop.tiktok.com/@' . $this->faker->userName() . '/product/' . $this->faker->numerify('#############'),
        ]);
    }

    /**
     * WhatsApp Business platform.
     */
    public function whatsapp(): static
    {
        return $this->state(fn(array $attributes) => [
            'platform' => 'whatsapp',
            'store_name' => 'WhatsApp Business',
            'product_url' => 'https://wa.me/' . $this->faker->numerify('62###########') . '?text=Halo,%20saya%20tertarik%20dengan%20produk%20ini',
        ]);
    }

    /**
     * Instagram platform.
     */
    public function instagram(): static
    {
        return $this->state(fn(array $attributes) => [
            'platform' => 'instagram',
            'store_name' => '@' . $this->faker->userName(),
            'product_url' => 'https://instagram.com/p/' . $this->faker->regexify('[A-Za-z0-9_-]{11}'),
        ]);
    }

    /**
     * Official website platform.
     */
    public function website(): static
    {
        return $this->state(fn(array $attributes) => [
            'platform' => 'website',
            'store_name' => 'Website Resmi',
            'product_url' => 'https://' . $this->faker->domainName() . '/product/' . $this->faker->slug(3),
        ]);
    }

    /**
     * Verified link.
     */
    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_verified' => true,
            'last_verified_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Popular link with high click count.
     */
    public function popular(): static
    {
        return $this->state(fn(array $attributes) => [
            'click_count' => $this->faker->numberBetween(100, 1000),
        ]);
    }

    /**
     * Recently verified link.
     */
    public function recentlyVerified(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_verified' => true,
            'last_verified_at' => $this->faker->dateTimeBetween('-3 days', 'now'),
        ]);
    }
}
