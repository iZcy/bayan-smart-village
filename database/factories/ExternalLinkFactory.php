<?php

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
            ],
            [
                'label' => 'WhatsApp',
                'icon' => 'whatsapp',
                'url' => 'https://wa.me/62' . $this->faker->randomNumber(8, true),
            ],
            [
                'label' => 'Facebook',
                'icon' => 'facebook',
                'url' => 'https://facebook.com/' . $this->faker->userName,
            ],
            [
                'label' => 'Website',
                'icon' => 'website',
                'url' => 'https://www.' . $this->faker->domainName,
            ],
            [
                'label' => 'Tokopedia',
                'icon' => 'tokopedia',
                'url' => 'https://tokopedia.com/' . $this->faker->userName,
            ],
            [
                'label' => 'Shopee',
                'icon' => 'shopee',
                'url' => 'https://shopee.co.id/' . $this->faker->userName,
            ],
            [
                'label' => 'GoJek',
                'icon' => 'gojek',
                'url' => 'https://gojek.com/merchant/' . $this->faker->userName,
            ],
            [
                'label' => 'YouTube',
                'icon' => 'youtube',
                'url' => 'https://youtube.com/@' . $this->faker->userName,
            ],
        ];

        $linkData = $this->faker->randomElement($linkTypes);

        return [
            'place_id' => SmeTourismPlace::factory(),
            'label' => $linkData['label'],
            'url' => $linkData['url'],
            'icon' => $linkData['icon'],
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function instagram(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Instagram',
            'icon' => 'instagram',
            'url' => 'https://instagram.com/' . $this->faker->userName,
        ]);
    }

    public function whatsapp(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'WhatsApp',
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

    public function facebook(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'url' => 'https://facebook.com/' . $this->faker->userName,
        ]);
    }
}
