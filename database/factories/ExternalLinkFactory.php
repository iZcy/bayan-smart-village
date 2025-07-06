<?php
// database/factories/ExternalLinkFactory.php

namespace Database\Factories;

use App\Models\ExternalLink;
use App\Models\SmeTourismPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalLinkFactory extends Factory
{
    protected $model = ExternalLink::class;

    public function definition(): array
    {
        $linkTypes = [
            [
                'label' => 'Instagram',
                'icon' => 'instagram',
                'url' => 'https://instagram.com/' . $this->faker->userName,
                'slug' => 'instagram',
            ],
            [
                'label' => 'WhatsApp',
                'icon' => 'whatsapp',
                'url' => 'https://wa.me/62' . $this->faker->randomNumber(8, true),
                'slug' => 'contact_person',
            ],
            [
                'label' => 'Website',
                'icon' => 'website',
                'url' => 'https://www.' . $this->faker->domainName,
                'slug' => 'website',
            ],
            [
                'label' => 'Tokopedia',
                'icon' => 'tokopedia',
                'url' => 'https://tokopedia.com/' . $this->faker->userName,
                'slug' => 'tokopedia',
            ],
            [
                'label' => 'Shopee',
                'icon' => 'shopee',
                'url' => 'https://shopee.co.id/' . $this->faker->userName,
                'slug' => 'shopee',
            ],
            [
                'label' => 'Menu Online',
                'icon' => 'link',
                'url' => 'https://example.com/menu/' . $this->faker->slug,
                'slug' => 'menu',
            ],
            [
                'label' => 'Facebook',
                'icon' => 'facebook',
                'url' => 'https://facebook.com/' . $this->faker->userName,
                'slug' => 'facebook',
            ],
            [
                'label' => 'Google Maps',
                'icon' => 'maps',
                'url' => 'https://maps.google.com/search/' . urlencode($this->faker->address),
                'slug' => 'lokasi',
            ],
        ];

        $linkData = $this->faker->randomElement($linkTypes);

        // Default: create a place and use its slug
        $place = SmeTourismPlace::inRandomOrder()->first() ?? SmeTourismPlace::factory()->create();

        return [
            'place_id' => $place->id,
            'label' => $linkData['label'],
            'url' => $linkData['url'],
            'icon' => $linkData['icon'],
            'subdomain' => $place->slug, // This should NOT be null
            'slug' => $linkData['slug'],
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    // Method to create links for a specific place
    public function forPlace(SmeTourismPlace $place): static
    {
        return $this->state(function (array $attributes) use ($place) {
            return [
                'place_id' => $place->id,
                'subdomain' => $place->slug, // Explicitly set subdomain
            ];
        });
    }

    // Method to create a specific type of link
    public function instagram(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Instagram',
            'icon' => 'instagram',
            'url' => 'https://instagram.com/' . $this->faker->userName,
            'slug' => 'instagram',
        ]);
    }

    public function whatsapp(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'WhatsApp',
            'icon' => 'whatsapp',
            'url' => 'https://wa.me/62' . $this->faker->randomNumber(8, true),
            'slug' => 'contact_person',
        ]);
    }

    public function website(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Website',
            'icon' => 'website',
            'url' => 'https://www.' . $this->faker->domainName,
            'slug' => 'website',
        ]);
    }

    public function tokopedia(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Tokopedia',
            'icon' => 'tokopedia',
            'url' => 'https://tokopedia.com/' . $this->faker->userName,
            'slug' => 'tokopedia',
        ]);
    }

    public function shopee(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Shopee',
            'icon' => 'shopee',
            'url' => 'https://shopee.co.id/' . $this->faker->userName,
            'slug' => 'shopee',
        ]);
    }

    public function menu(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Menu Online',
            'icon' => 'link',
            'url' => 'https://example.com/menu/' . $this->faker->slug,
            'slug' => 'menu',
        ]);
    }

    public function maps(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Google Maps',
            'icon' => 'maps',
            'url' => 'https://maps.google.com/search/' . urlencode($this->faker->address),
            'slug' => 'lokasi',
        ]);
    }
}
