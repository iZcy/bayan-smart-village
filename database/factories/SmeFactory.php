<?php

namespace Database\Factories;

use App\Models\Sme;
use App\Models\Community;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sme>
 */
class SmeFactory extends Factory
{
    protected $model = Sme::class;

    public function definition(): array
    {
        $productBusinesses = [
            'Kerajinan Bambu Berkah',
            'Tenun Indah Nusantara',
            'Batik Tulis Asli',
            'Keramik Cantik Desa',
            'Madu Murni Pegunungan',
            'Kopi Arabika Premium',
            'Dodol Khas Daerah',
            'Emping Melinjo Crispy',
            'Keripik Singkong Gurih',
            'Anyaman Pandan Kreatif',
            'Ukiran Kayu Artistik',
            'Songket Emas'
        ];

        $serviceBusinesses = [
            'Wisata Desa Adventure',
            'Homestay Keluarga Bahagia',
            'Warung Nasi Gudeg Bu Sri',
            'Ojek Wisata Friendly',
            'Jasa Foto Prewedding',
            'Spa Tradisional Sehat',
            'Pelatihan Kerajinan Lokal',
            'Bengkel Motor Jujur',
            'Salon Kecantikan Modern',
            'Catering Masakan Daerah',
            'Laundry Kilat Bersih',
            'Kursus Bahasa Inggris'
        ];

        $type = $this->faker->randomElement(['product', 'service']);
        $businesses = $type === 'product' ? $productBusinesses : $serviceBusinesses;
        $name = $this->faker->randomElement($businesses);

        return [
            'community_id' => Community::factory(),
            'place_id' => $this->faker->optional(0.4)->randomElement(Place::pluck('id')->toArray() ?: [Place::factory()->create()->id]),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(2, true),
            'type' => $type,
            'owner_name' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->optional(0.7)->email(),
            'logo_url' => $this->faker->optional(0.5)->imageUrl(300, 300, 'business', true, 'logo'),
            'business_hours' => [
                'monday' => '08:00 - 17:00',
                'tuesday' => '08:00 - 17:00',
                'wednesday' => '08:00 - 17:00',
                'thursday' => '08:00 - 17:00',
                'friday' => '08:00 - 17:00',
                'saturday' => '08:00 - 15:00',
                'sunday' => $this->faker->randomElement(['Tutup', '09:00 - 14:00']),
            ],
            'is_verified' => $this->faker->boolean(60), // 60% chance of being verified
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
        ];
    }

    /**
     * Indicate that the SME belongs to a specific community.
     */
    public function forCommunity(Community $community): static
    {
        return $this->state(fn(array $attributes) => [
            'community_id' => $community->id,
        ]);
    }

    /**
     * Indicate that the SME is located at a specific place.
     */
    public function atPlace(Place $place): static
    {
        return $this->state(fn(array $attributes) => [
            'place_id' => $place->id,
        ]);
    }

    /**
     * Product-focused SME.
     */
    public function product(): static
    {
        $productBusinesses = [
            'Kerajinan Bambu Berkah',
            'Tenun Indah Nusantara',
            'Batik Tulis Asli',
            'Keramik Cantik Desa',
            'Madu Murni Pegunungan',
            'Kopi Arabika Premium',
            'Dodol Khas Daerah',
            'Emping Melinjo Crispy',
            'Keripik Singkong Gurih'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($productBusinesses),
            'type' => 'product',
        ]);
    }

    /**
     * Service-focused SME.
     */
    public function service(): static
    {
        $serviceBusinesses = [
            'Wisata Desa Adventure',
            'Homestay Keluarga Bahagia',
            'Warung Nasi Gudeg Bu Sri',
            'Ojek Wisata Friendly',
            'Jasa Foto Prewedding',
            'Spa Tradisional Sehat',
            'Pelatihan Kerajinan Lokal',
            'Bengkel Motor Jujur',
            'Salon Kecantikan Modern'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => $this->faker->randomElement($serviceBusinesses),
            'type' => 'service',
        ]);
    }

    /**
     * Verified SME.
     */
    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Unverified SME.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_verified' => false,
        ]);
    }

    /**
     * Active SME.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Inactive SME.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
