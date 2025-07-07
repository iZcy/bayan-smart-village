<?php

namespace Database\Factories;

use App\Models\ExternalLink;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalLinkFactory extends Factory
{
    protected $model = ExternalLink::class;

    public function definition(): array
    {
        $isLocal = app()->environment('local');

        // Different link types based on environment
        $linkTypes = $this->getLinkTypesByEnvironment($isLocal);
        $linkData = $this->faker->randomElement($linkTypes);

        return [
            'label' => $linkData['label'],
            'url' => $linkData['url'],
            'icon' => $linkData['icon'],
            'slug' => $this->faker->unique()->slug(1),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'description' => $this->faker->optional()->sentence(),
            'click_count' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'expires_at' => $this->faker->optional(0.2)->dateTimeBetween('now', '+1 year'), // 20% chance of having expiration
        ];
    }

    private function getLinkTypesByEnvironment(bool $isLocal): array
    {
        if ($isLocal) {
            return [
                [
                    'label' => 'Local Development Server',
                    'icon' => 'website',
                    'url' => 'http://localhost:' . $this->faker->randomElement(['3000', '8000', '8080', '9000']),
                ],
                [
                    'label' => 'Local API Test',
                    'icon' => 'link',
                    'url' => 'http://127.0.0.1:8000/api/test',
                ],
                [
                    'label' => 'Development WhatsApp',
                    'icon' => 'whatsapp',
                    'url' => 'http://localhost:8000/test/whatsapp',
                ],
                [
                    'label' => 'Local File Server',
                    'icon' => 'website',
                    'url' => 'http://localhost:8080/files',
                ],
                [
                    'label' => 'Mock Instagram',
                    'icon' => 'instagram',
                    'url' => 'http://httpbin.org/json',
                ],
                [
                    'label' => 'Test Email Service',
                    'icon' => 'email',
                    'url' => 'http://localhost:1080', // MailHog default port
                ],
                [
                    'label' => 'Development Database',
                    'icon' => 'link',
                    'url' => 'http://localhost:8080/adminer', // Adminer default
                ],
                [
                    'label' => 'Local Maps Service',
                    'icon' => 'maps',
                    'url' => 'http://localhost:3001/maps',
                ],
            ];
        } else {
            return [
                [
                    'label' => 'Instagram Profile',
                    'icon' => 'instagram',
                    'url' => 'https://instagram.com/' . $this->faker->userName,
                ],
                [
                    'label' => 'WhatsApp Contact',
                    'icon' => 'whatsapp',
                    'url' => 'https://wa.me/62' . $this->faker->randomNumber(8, true),
                ],
                [
                    'label' => 'Website',
                    'icon' => 'website',
                    'url' => 'https://www.' . $this->faker->domainName,
                ],
                [
                    'label' => 'Tokopedia Store',
                    'icon' => 'tokopedia',
                    'url' => 'https://tokopedia.com/' . $this->faker->userName,
                ],
                [
                    'label' => 'Shopee Store',
                    'icon' => 'shopee',
                    'url' => 'https://shopee.co.id/' . $this->faker->userName,
                ],
                [
                    'label' => 'Online Menu',
                    'icon' => 'link',
                    'url' => 'https://example.com/menu/' . $this->faker->slug,
                ],
                [
                    'label' => 'Facebook Page',
                    'icon' => 'facebook',
                    'url' => 'https://facebook.com/' . $this->faker->userName,
                ],
                [
                    'label' => 'Google Maps Location',
                    'icon' => 'maps',
                    'url' => 'https://maps.google.com/search/' . urlencode($this->faker->address),
                ],
                [
                    'label' => 'YouTube Channel',
                    'icon' => 'youtube',
                    'url' => 'https://youtube.com/channel/' . $this->faker->regexify('[a-zA-Z0-9]{24}'),
                ],
                [
                    'label' => 'TikTok Profile',
                    'icon' => 'tiktok',
                    'url' => 'https://tiktok.com/@' . $this->faker->userName,
                ],
                [
                    'label' => 'Email Contact',
                    'icon' => 'email',
                    'url' => 'mailto:' . $this->faker->email,
                ],
                [
                    'label' => 'Telegram Channel',
                    'icon' => 'telegram',
                    'url' => 'https://t.me/' . $this->faker->userName,
                ],
            ];
        }
    }

    // Method to create a link for a specific village
    public function forVillage(Village $village): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => $village->id,
        ]);
    }

    // Method to create an apex domain link (no village)
    public function apexDomain(): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => null,
        ]);
    }

    // Method to create environment-specific link types
    public function localDevelopment(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Local Dev Environment',
            'icon' => 'website',
            'url' => 'http://localhost:' . fake()->randomElement(['3000', '8000', '8080']),
        ]);
    }

    public function production(): static
    {
        return $this->state(fn(array $attributes) => [
            'url' => 'https://' . fake()->domainName,
        ]);
    }

    // Method to create a specific type of link
    public function instagram(): static
    {
        $url = app()->environment('local')
            ? 'http://localhost:8000/test/instagram'
            : 'https://instagram.com/' . $this->faker->userName;

        return $this->state(fn(array $attributes) => [
            'label' => 'Instagram Profile',
            'icon' => 'instagram',
            'url' => $url,
        ]);
    }

    public function whatsapp(): static
    {
        $url = app()->environment('local')
            ? 'http://localhost:8000/test/whatsapp'
            : 'https://wa.me/62' . $this->faker->randomNumber(8, true);

        return $this->state(fn(array $attributes) => [
            'label' => 'WhatsApp Contact',
            'icon' => 'whatsapp',
            'url' => $url,
        ]);
    }

    public function website(): static
    {
        $url = app()->environment('local')
            ? 'http://localhost:' . fake()->randomElement(['3000', '8080'])
            : 'https://www.' . $this->faker->domainName;

        return $this->state(fn(array $attributes) => [
            'label' => 'Website',
            'icon' => 'website',
            'url' => $url,
        ]);
    }

    public function tokopedia(): static
    {
        $url = app()->environment('local')
            ? 'http://localhost:8000/test/tokopedia'
            : 'https://tokopedia.com/' . $this->faker->userName;

        return $this->state(fn(array $attributes) => [
            'label' => 'Tokopedia Store',
            'icon' => 'tokopedia',
            'url' => $url,
        ]);
    }

    public function shopee(): static
    {
        $url = app()->environment('local')
            ? 'http://localhost:8000/test/shopee'
            : 'https://shopee.co.id/' . $this->faker->userName;

        return $this->state(fn(array $attributes) => [
            'label' => 'Shopee Store',
            'icon' => 'shopee',
            'url' => $url,
        ]);
    }

    public function menu(): static
    {
        $url = app()->environment('local')
            ? 'http://localhost:8000/test/menu'
            : 'https://example.com/menu/' . $this->faker->slug;

        return $this->state(fn(array $attributes) => [
            'label' => 'Online Menu',
            'icon' => 'link',
            'url' => $url,
        ]);
    }

    public function maps(): static
    {
        $url = app()->environment('local')
            ? 'http://localhost:8000/test/maps'
            : 'https://maps.google.com/search/' . urlencode($this->faker->address);

        return $this->state(fn(array $attributes) => [
            'label' => 'Google Maps Location',
            'icon' => 'maps',
            'url' => $url,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withExpiration(): static
    {
        return $this->state(fn(array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
        ]);
    }
}
