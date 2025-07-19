<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    private static $usedSlugs = [];

    public function definition(): array
    {
        $articleTitles = [
            'Pesona Alam Desa Yang Memukau Wisatawan',
            'Tradisi Unik Yang Masih Terjaga Hingga Kini',
            'Kerajinan Tangan Warisan Nenek Moyang',
            'Festival Budaya Tahunan Yang Meriah',
            'Kuliner Khas Daerah Yang Menggugah Selera',
            'Wisata Edukasi Yang Mendidik Dan Menyenangkan',
            'Kehidupan Masyarakat Desa Yang Harmonis',
            'Inovasi Teknologi Ramah Lingkungan',
            'Potensi Ekonomi Kreatif Yang Berkembang',
            'Cerita Sejarah Yang Menginspirasi',
            'Kearifan Lokal Dalam Menjaga Lingkungan',
            'Pemberdayaan Masyarakat Melalui UMKM',
            'Keindahan Arsitektur Tradisional',
            'Ritual Adat Yang Sakral',
            'Permainan Tradisional Anak-anak',
            'Musik Dan Tarian Khas Daerah',
            'Makanan Fermentasi Tradisional',
            'Pengobatan Herbal Turun Temurun',
            'Teknik Bertani Organik',
            'Seni Ukir Yang Menawan'
        ];

        $title = $this->faker->randomElement($articleTitles);
        $baseSlug = Str::slug($title);

        // Generate realistic article content
        $content = $this->generateArticleContent();

        return [
            'village_id' => $this->faker->optional(0.7)->randomElement(Village::pluck('id')->toArray() ?: [Village::factory()->create()->id]),
            'community_id' => $this->faker->optional(0.5)->randomElement(Community::pluck('id')->toArray() ?: [null]),
            'sme_id' => $this->faker->optional(0.3)->randomElement(Sme::pluck('id')->toArray() ?: [null]),
            'place_id' => $this->faker->optional(0.4)->randomElement(Place::pluck('id')->toArray() ?: [null]),
            'title' => $title,
            'slug' => $this->generateUniqueSlug('general', $baseSlug),
            'content' => $content,
            'cover_image_url' => $this->faker->optional(0.8)->imageUrl(800, 500, 'nature', true, 'article'),
            'is_featured' => $this->faker->boolean(25), // 25% chance of being featured
            'is_published' => $this->faker->boolean(90), // 90% chance of being published
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
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
     * Generate realistic article content.
     */
    private function generateArticleContent(): string
    {
        $paragraphs = [];

        // Introduction paragraph
        $paragraphs[] = $this->faker->paragraph(4);

        // 3-5 body paragraphs
        for ($i = 0; $i < $this->faker->numberBetween(3, 5); $i++) {
            $paragraphs[] = $this->faker->paragraph($this->faker->numberBetween(3, 6));
        }

        // Conclusion paragraph
        $paragraphs[] = $this->faker->paragraph(3);

        return '<p>' . implode('</p><p>', $paragraphs) . '</p>';
    }

    /**
     * Indicate that the article belongs to a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(function (array $attributes) use ($village) {
            $baseSlug = Str::slug($attributes['title']);
            $slug = $this->generateUniqueSlug('village-' . $village->id, $baseSlug);

            return [
                'village_id' => $village->id,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Indicate that the article belongs to a specific community.
     */
    public function forCommunity(Community $community): static
    {
        return $this->state(function (array $attributes) use ($community) {
            $baseSlug = Str::slug($attributes['title']);
            $slug = $this->generateUniqueSlug('community-' . $community->id, $baseSlug);

            return [
                'community_id' => $community->id,
                'village_id' => $community->village_id,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Indicate that the article belongs to a specific SME.
     */
    public function forSme(Sme $sme): static
    {
        return $this->state(function (array $attributes) use ($sme) {
            $baseSlug = Str::slug($attributes['title']);
            $slug = $this->generateUniqueSlug('sme-' . $sme->id, $baseSlug);

            return [
                'sme_id' => $sme->id,
                'community_id' => $sme->community_id,
                'village_id' => $sme->community->village_id,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Indicate that the article is about a specific place.
     */
    public function aboutPlace(Place $place): static
    {
        return $this->state(function (array $attributes) use ($place) {
            $baseSlug = Str::slug($attributes['title']);
            $slug = $this->generateUniqueSlug('place-' . $place->id, $baseSlug);

            return [
                'place_id' => $place->id,
                'village_id' => $place->village_id,
                'slug' => $slug,
            ];
        });
    }

    /**
     * Featured article.
     */
    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Published article.
     */
    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_published' => true,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Draft article.
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Tourism-related article.
     */
    public function tourism(): static
    {
        $tourismTitles = [
            'Destinasi Wisata Tersembunyi Yang Wajib Dikunjungi',
            'Petualangan Seru Di Alam Bebas',
            'Wisata Kuliner Yang Tak Terlupakan',
            'Festival Budaya Yang Memukau Mata',
            'Keindahan Alam Yang Menawan Hati',
            'Pengalaman Menginap Di Homestay Tradisional',
            'Aktivitas Menarik Untuk Wisatawan',
            'Spot Foto Instagramable Di Desa'
        ];

        return $this->state(function (array $attributes) use ($tourismTitles) {
            $title = $this->faker->randomElement($tourismTitles);
            $baseSlug = Str::slug($title);

            return [
                'title' => $title,
                'slug' => $this->generateUniqueSlug('tourism', $baseSlug),
            ];
        });
    }

    /**
     * Culture-related article.
     */
    public function culture(): static
    {
        $cultureTitles = [
            'Warisan Budaya Yang Harus Dilestarikan',
            'Tradisi Turun Temurun Yang Masih Hidup',
            'Kearifan Lokal Dalam Kehidupan Sehari-hari',
            'Seni Dan Budaya Yang Mengakar Kuat',
            'Cerita Rakyat Yang Sarat Makna',
            'Upacara Adat Yang Sakral',
            'Kesenian Tradisional Yang Menawan',
            'Filosofi Hidup Masyarakat Desa'
        ];

        return $this->state(function (array $attributes) use ($cultureTitles) {
            $title = $this->faker->randomElement($cultureTitles);
            $baseSlug = Str::slug($title);

            return [
                'title' => $title,
                'slug' => $this->generateUniqueSlug('culture', $baseSlug),
            ];
        });
    }

    /**
     * Business-related article.
     */
    public function business(): static
    {
        $businessTitles = [
            'Kisah Sukses UMKM Lokal Yang Menginspirasi',
            'Inovasi Produk Yang Menembus Pasar Global',
            'Strategi Pemasaran Yang Efektif Untuk UMKM',
            'Kemitraan Yang Saling Menguntungkan',
            'Pemberdayaan Ekonomi Masyarakat Desa',
            'Transformasi Digital UMKM Tradisional',
            'Peluang Bisnis Di Era Modern',
            'Kolaborasi Komunitas Untuk Kemajuan Ekonomi'
        ];

        return $this->state(function (array $attributes) use ($businessTitles) {
            $title = $this->faker->randomElement($businessTitles);
            $baseSlug = Str::slug($title);

            return [
                'title' => $title,
                'slug' => $this->generateUniqueSlug('business', $baseSlug),
            ];
        });
    }

    /**
     * Reset used slugs tracker (useful for testing).
     */
    public static function resetUsedSlugs(): void
    {
        self::$usedSlugs = [];
    }
}
