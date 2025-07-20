<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    private static $usedCategories = [];
    private static $usedSlugs = [];

    public function definition(): array
    {
        $productCategories = [
            'Kerajinan Tangan' => 'heroicon-o-cube',
            'Makanan & Minuman' => 'heroicon-o-cake',
            'Tekstil & Pakaian' => 'heroicon-o-scissors',
            'Perhiasan' => 'heroicon-o-gem',
            'Furniture' => 'heroicon-o-home',
            'Keramik & Gerabah' => 'heroicon-o-beaker',
            'Produk Pertanian' => 'heroicon-o-leaf',
            'Produk Perikanan' => 'heroicon-o-fish',
            'Olahan Susu' => 'heroicon-o-milk',
            'Rempah-rempah' => 'heroicon-o-fire',
            'Batik & Tenun' => 'heroicon-o-sparkles',
            'Anyaman & Rotan' => 'heroicon-o-squares-2x2',
            'Ukiran Kayu' => 'heroicon-o-cube-transparent',
            'Produk Bambu' => 'heroicon-o-building-office-2',
            'Kopi & Teh' => 'heroicon-o-coffee-cup',
        ];

        $serviceCategories = [
            'Jasa Pemandu Wisata' => 'heroicon-o-map',
            'Transportasi' => 'heroicon-o-truck',
            'Penginapan' => 'heroicon-o-building-office',
            'Kuliner' => 'heroicon-o-utensils',
            'Jasa Fotografi' => 'heroicon-o-camera',
            'Spa & Wellness' => 'heroicon-o-heart',
            'Jasa Pertanian' => 'heroicon-o-wrench-screwdriver',
            'Jasa Konstruksi' => 'heroicon-o-hammer',
            'Pendidikan & Pelatihan' => 'heroicon-o-academic-cap',
            'Jasa Keuangan' => 'heroicon-o-banknotes',
            'Jasa Laundry' => 'heroicon-o-sparkles',
            'Jasa Repair' => 'heroicon-o-wrench',
            'Event Organizer' => 'heroicon-o-calendar-days',
            'Konsultasi' => 'heroicon-o-chat-bubble-left-right',
            'Jasa Cleaning' => 'heroicon-o-swatch',
        ];

        $type = $this->faker->randomElement(['product', 'service']);
        $categories = $type === 'product' ? $productCategories : $serviceCategories;

        // Get a unique name for this village
        $villageId = $this->faker->randomElement(Village::pluck('id')->toArray() ?: [Village::factory()->create()->id]);
        $name = $this->getUniqueNameForVillage($villageId, $categories);
        $icon = $categories[$name];
        $slug = $this->generateUniqueSlug($name);

        return [
            'village_id' => $villageId,
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'description' => $this->faker->sentence(),
            'icon' => $icon,
        ];
    }

    /**
     * Generate unique slug globally (since slug is unique in migration).
     */
    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Keep trying until we get a unique slug
        while (in_array($slug, self::$usedSlugs)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Mark slug as used globally
        self::$usedSlugs[] = $slug;

        return $slug;
    }

    /**
     * Get a unique category name for a specific village.
     */
    private function getUniqueNameForVillage(string $villageId, array $categories): string
    {
        // Initialize village key if not exists
        if (!isset(self::$usedCategories[$villageId])) {
            self::$usedCategories[$villageId] = [];
        }

        // Get available categories for this village
        $availableCategories = array_diff(array_keys($categories), self::$usedCategories[$villageId]);

        // If no available categories, reset and start over (shouldn't happen in normal use)
        if (empty($availableCategories)) {
            self::$usedCategories[$villageId] = [];
            $availableCategories = array_keys($categories);
        }

        // Pick a random available category
        $selectedName = $this->faker->randomElement($availableCategories);

        // Mark it as used for this village
        self::$usedCategories[$villageId][] = $selectedName;

        return $selectedName;
    }

    /**
     * Indicate that the category belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => $village->id,
        ]);
    }

    /**
     * Product category type.
     */
    public function product(): static
    {
        $productCategories = [
            'Kerajinan Tangan' => 'heroicon-o-cube',
            'Makanan & Minuman' => 'heroicon-o-cake',
            'Tekstil & Pakaian' => 'heroicon-o-scissors',
            'Perhiasan' => 'heroicon-o-gem',
            'Furniture' => 'heroicon-o-home',
            'Keramik & Gerabah' => 'heroicon-o-beaker',
            'Produk Pertanian' => 'heroicon-o-leaf',
            'Produk Perikanan' => 'heroicon-o-fish',
            'Batik & Tenun' => 'heroicon-o-sparkles',
            'Anyaman & Rotan' => 'heroicon-o-squares-2x2',
            'Ukiran Kayu' => 'heroicon-o-cube-transparent',
            'Produk Bambu' => 'heroicon-o-building-office-2',
            'Kopi & Teh' => 'heroicon-o-coffee-cup',
        ];

        return $this->state(function (array $attributes) use ($productCategories) {
            $villageId = $attributes['village_id'] ?? Village::factory()->create()->id;
            $name = $this->getUniqueNameForVillage($villageId, $productCategories);
            $icon = $productCategories[$name];
            $slug = $this->generateUniqueSlug($name);

            return [
                'name' => $name,
                'slug' => $slug,
                'type' => 'product',
                'icon' => $icon,
                'village_id' => $villageId,
            ];
        });
    }

    /**
     * Service category type.
     */
    public function service(): static
    {
        $serviceCategories = [
            'Jasa Pemandu Wisata' => 'heroicon-o-map',
            'Transportasi' => 'heroicon-o-truck',
            'Penginapan' => 'heroicon-o-building-office',
            'Kuliner' => 'heroicon-o-utensils',
            'Jasa Fotografi' => 'heroicon-o-camera',
            'Spa & Wellness' => 'heroicon-o-heart',
            'Jasa Pertanian' => 'heroicon-o-wrench-screwdriver',
            'Pendidikan & Pelatihan' => 'heroicon-o-academic-cap',
            'Jasa Laundry' => 'heroicon-o-sparkles',
            'Jasa Repair' => 'heroicon-o-wrench',
            'Event Organizer' => 'heroicon-o-calendar-days',
            'Konsultasi' => 'heroicon-o-chat-bubble-left-right',
            'Jasa Cleaning' => 'heroicon-o-swatch',
        ];

        return $this->state(function (array $attributes) use ($serviceCategories) {
            $villageId = $attributes['village_id'] ?? Village::factory()->create()->id;
            $name = $this->getUniqueNameForVillage($villageId, $serviceCategories);
            $icon = $serviceCategories[$name];
            $slug = $this->generateUniqueSlug($name);

            return [
                'name' => $name,
                'slug' => $slug,
                'type' => 'service',
                'icon' => $icon,
                'village_id' => $villageId,
            ];
        });
    }

    /**
     * Reset used categories tracker (useful for testing).
     */
    public static function resetUsedCategories(): void
    {
        self::$usedCategories = [];
        self::$usedSlugs = [];
    }
}
