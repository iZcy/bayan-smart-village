<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Community>
 */
class CommunityFactory extends Factory
{
    protected $model = Community::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'village_id' => Village::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(2),
            'domain' => $this->faker->optional(0.2)->domainName(),
            'logo_url' => $this->faker->optional(0.6)->imageUrl(300, 300, 'business', true, 'logo'),
            'contact_person' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->email(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Indicate that the community belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => $village->id,
        ]);
    }

    /**
     * Indicate that the community is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the community is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the community has a custom domain.
     */
    public function withCustomDomain(): static
    {
        return $this->state(fn(array $attributes) => [
            'domain' => $this->faker->domainName(),
        ]);
    }
}
