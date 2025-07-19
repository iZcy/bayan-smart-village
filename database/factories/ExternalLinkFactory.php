<?php

namespace Database\Factories;

use App\Models\ExternalLink;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExternalLink>
 */
class ExternalLinkFactory extends Factory
{
    protected $model = ExternalLink::class;

    private static $usedSlugs = [];

    public function definition(): array
    {
        $linkTypes = [
            'social' => [
                'Instagram Official' => 'https://instagram.com/' . $this->faker->userName(),
                'Facebook Page' => 'https://facebook.com/' . $this->faker->userName(),
                'YouTube Channel' => 'https://youtube.com/c/' . $this->faker->userName(),
                'TikTok Official' => 'https://tiktok.com/@' . $this->faker->userName(),
                'WhatsApp Business' => 'https://wa.me/' . $this->faker->numerify('62###########'),
                'Twitter Account' => 'https://twitter.com/' . $this->faker->userName(),
                'LinkedIn Page' => 'https://linkedin.com/company/' . $this->faker->userName(),
            ],
            'marketplace' => [
                'Toko Tokopedia' => 'https://tokopedia.com/shop/' . $this->faker->userName(),
                'Shopee Store' => 'https://shopee.co.id/shop/' . $this->faker->userName(),
                'Bukalapak Store' => 'https://bukalapak.com/u/' . $this->faker->userName(),
                'TikTok Shop' => 'https://shop.tiktok.com/' . $this->faker->userName(),
                'Lazada Store' => 'https://lazada.co.id/shop/' . $this->faker->userName(),
                'Blibli Store' => 'https://blibli.com/merchant/' . $this->faker->userName(),
            ],
            'booking' => [
                'Booking Homestay' => 'https://booking.com/hotel/' . $this->faker->slug(),
                'Airbnb Listing' => 'https://airbnb.com/rooms/' . $this->faker->numerify('########'),
                'Traveloka Hotel' => 'https://traveloka.com/hotel/' . $this->faker->slug(),
                'Agoda Booking' => 'https://agoda.com/hotel/' . $this->faker->slug(),
            ],
            'maps' => [
                'Lokasi di Google Maps' => 'https://maps.google.com/place/' . $this->faker->slug(),
                'Alamat Lengkap' => 'https://goo.gl/maps/' . $this->faker->regexify('[A-Za-z0-9]{12}'),
                'Waze Location' => 'https://waze.com/ul/' . $this->faker->regexify('[A-Za-z0-9]{10}'),
            ],
            'website' => [
                'Website Resmi' => 'https://' . $this->faker->domainName(),
                'Blog Resmi' => 'https://blog.' . $this->faker->domainName(),
                'Portal Informasi' => 'https://info.' . $this->faker->domainName(),
                'Online Catalog' => 'https://catalog.' . $this->faker->domainName(),
            ]
        ];

        $type = $this->faker->randomKey($linkTypes);
        $links = $linkTypes[$type];
        $label = $this->faker->randomKey($links);
        $url = $links[$label];

        $icons = [
            'Instagram Official' => 'heroicon-o-camera',
            'Facebook Page' => 'heroicon-o-users',
            'YouTube Channel' => 'heroicon-o-play',
            'TikTok Official' => 'heroicon-o-musical-note',
            'WhatsApp Business' => 'heroicon-o-chat-bubble-left-right',
            'Twitter Account' => 'heroicon-o-at-symbol',
            'LinkedIn Page' => 'heroicon-o-briefcase',
            'Toko Tokopedia' => 'heroicon-o-shopping-bag',
            'Shopee Store' => 'heroicon-o-shopping-cart',
            'Bukalapak Store' => 'heroicon-o-building-storefront',
            'TikTok Shop' => 'heroicon-o-gift',
            'Lazada Store' => 'heroicon-o-truck',
            'Blibli Store' => 'heroicon-o-cube',
            'Booking Homestay' => 'heroicon-o-home',
            'Airbnb Listing' => 'heroicon-o-building-office',
            'Traveloka Hotel' => 'heroicon-o-map-pin',
            'Agoda Booking' => 'heroicon-o-calendar-days',
            'Lokasi di Google Maps' => 'heroicon-o-map',
            'Alamat Lengkap' => 'heroicon-o-map-pin',
            'Waze Location' => 'heroicon-o-map-pin',
            'Website Resmi' => 'heroicon-o-globe-alt',
            'Blog Resmi' => 'heroicon-o-document-text',
            'Portal Informasi' => 'heroicon-o-information-circle',
            'Online Catalog' => 'heroicon-o-book-open',
        ];

        // Generate unique slug
        $baseSlug = Str::slug($label . '-' . $this->faker->word());
        $slug = $this->generateUniqueSlug('general', $baseSlug);

        return [
            'village_id' => $this->faker->optional(0.4)->randomElement(Village::pluck('id')->toArray() ?: [Village::factory()->create()->id]),
            'community_id' => $this->faker->optional(0.3)->randomElement(Community::pluck('id')->toArray() ?: [null]),
            'sme_id' => $this->faker->optional(0.3)->randomElement(Sme::pluck('id')->toArray() ?: [null]),
            'label' => $label,
            'url' => $url,
            'icon' => $icons[$label] ?? 'heroicon-o-link',
            'slug' => $slug,
            'sort_order' => $this->faker->numberBetween(0, 100),
            'description' => $this->faker->optional(0.6)->sentence(),
            'click_count' => $this->faker->numberBetween(0, 500),
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
            'expires_at' => $this->faker->optional(0.1)->dateTimeBetween('now', '+1 year'), // 10% chance of expiration
        ];
    }

    /**
     * Generate unique slug for the appropriate scope.
     */
    private function generateUniqueSlug(string $scope, string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;

        // Initialize scope key if not exists
        if (!isset(self::$usedSlugs[$scope])) {
            self::$usedSlugs[$scope] = [];
        }

        // Keep trying until we get a unique slug
        while (in_array($slug, self::$usedSlugs[$scope])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Mark slug as used for this scope
        self::$usedSlugs[$scope][] = $slug;

        return $slug;
    }

    /**
     * Indicate that the link belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(function (array $attributes) use ($village) {
            $baseSlug = Str::slug($attributes['label'] . '-' . $this->faker->word());
            $slug = $this->generateUniqueSlug('village-' . $village->id, $baseSlug);

            return [
                'village_id' => $village->id,
                'community_id' => null,
                'sme_id' => null,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Indicate that the link belongs to a specific community.
     */
    public function forCommunity(Community $community): static
    {
        return $this->state(function (array $attributes) use ($community) {
            $baseSlug = Str::slug($attributes['label'] . '-' . $this->faker->word());
            $slug = $this->generateUniqueSlug('community-' . $community->id, $baseSlug);

            return [
                'village_id' => null,
                'community_id' => $community->id,
                'sme_id' => null,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Indicate that the link belongs to a specific SME.
     */
    public function forSme(Sme $sme): static
    {
        return $this->state(function (array $attributes) use ($sme) {
            $baseSlug = Str::slug($attributes['label'] . '-' . $this->faker->word());
            $slug = $this->generateUniqueSlug('sme-' . $sme->id, $baseSlug);

            return [
                'village_id' => null,
                'community_id' => null,
                'sme_id' => $sme->id,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Social media link.
     */
    public function socialMedia(): static
    {
        $socialLinks = [
            'Instagram Official' => [
                'url' => 'https://instagram.com/' . $this->faker->userName(),
                'icon' => 'heroicon-o-camera'
            ],
            'Facebook Page' => [
                'url' => 'https://facebook.com/' . $this->faker->userName(),
                'icon' => 'heroicon-o-users'
            ],
            'WhatsApp Business' => [
                'url' => 'https://wa.me/' . $this->faker->numerify('62###########'),
                'icon' => 'heroicon-o-chat-bubble-left-right'
            ],
            'TikTok Official' => [
                'url' => 'https://tiktok.com/@' . $this->faker->userName(),
                'icon' => 'heroicon-o-musical-note'
            ],
        ];

        $label = $this->faker->randomKey($socialLinks);
        $data = $socialLinks[$label];

        return $this->state(fn(array $attributes) => [
            'label' => $label,
            'url' => $data['url'],
            'icon' => $data['icon'],
        ]);
    }

    /**
     * Marketplace link.
     */
    public function marketplace(): static
    {
        $marketplaceLinks = [
            'Toko Tokopedia' => [
                'url' => 'https://tokopedia.com/shop/' . $this->faker->userName(),
                'icon' => 'heroicon-o-shopping-bag'
            ],
            'Shopee Store' => [
                'url' => 'https://shopee.co.id/shop/' . $this->faker->userName(),
                'icon' => 'heroicon-o-shopping-cart'
            ],
            'TikTok Shop' => [
                'url' => 'https://shop.tiktok.com/' . $this->faker->userName(),
                'icon' => 'heroicon-o-gift'
            ],
        ];

        $label = $this->faker->randomKey($marketplaceLinks);
        $data = $marketplaceLinks[$label];

        return $this->state(fn(array $attributes) => [
            'label' => $label,
            'url' => $data['url'],
            'icon' => $data['icon'],
        ]);
    }

    /**
     * Popular link with high click count.
     */
    public function popular(): static
    {
        return $this->state(fn(array $attributes) => [
            'click_count' => $this->faker->numberBetween(100, 2000),
        ]);
    }

    /**
     * Temporary link with expiration.
     */
    public function temporary(): static
    {
        return $this->state(fn(array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('now', '+6 months'),
        ]);
    }

    /**
     * Permanent link without expiration.
     */
    public function permanent(): static
    {
        return $this->state(fn(array $attributes) => [
            'expires_at' => null,
        ]);
    }

    /**
     * Reset used slugs tracker (useful for testing).
     */
    public static function resetUsedSlugs(): void
    {
        self::$usedSlugs = [];
    }
}
