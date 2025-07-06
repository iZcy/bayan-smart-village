<?php
// database/factories/ExternalLinkFactory.php

namespace Database\Factories;

use App\Models\ExternalLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalLinkFactory extends Factory
{
    protected $model = ExternalLink::class;

    public function definition(): array
    {
        $linkTypes = [
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
        ];

        $linkData = $this->faker->randomElement($linkTypes);

        return [
            'label' => $linkData['label'],
            'url' => $linkData['url'],
            'icon' => $linkData['icon'],
            'subdomain' => $this->faker->unique()->slug(2),
            'slug' => $this->faker->unique()->slug(1),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'description' => $this->faker->optional()->sentence(),
            'click_count' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'expires_at' => $this->faker->optional(0.2)->dateTimeBetween('now', '+1 year'), // 20% chance of having expiration
        ];
    }

    // Method to create a specific type of link
    public function instagram(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Instagram Profile',
            'icon' => 'instagram',
            'url' => 'https://instagram.com/' . $this->faker->userName,
        ]);
    }

    public function whatsapp(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'WhatsApp Contact',
            'icon' => 'whatsapp',
            'url' => 'https://wa.me/62' . $this->faker->randomNumber(8, true),
        ]);
    }

    public function website(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Website',
            'icon' => 'website',
            'url' => 'https://www.' . $this->faker->domainName,
        ]);
    }

    public function tokopedia(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Tokopedia Store',
            'icon' => 'tokopedia',
            'url' => 'https://tokopedia.com/' . $this->faker->userName,
        ]);
    }

    public function shopee(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Shopee Store',
            'icon' => 'shopee',
            'url' => 'https://shopee.co.id/' . $this->faker->userName,
        ]);
    }

    public function menu(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Online Menu',
            'icon' => 'link',
            'url' => 'https://example.com/menu/' . $this->faker->slug,
        ]);
    }

    public function maps(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Google Maps Location',
            'icon' => 'maps',
            'url' => 'https://maps.google.com/search/' . urlencode($this->faker->address),
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
