<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\SmeTourismPlace;
use App\Models\Village;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating articles...');

        $villages = Village::active()->with('places')->get();

        if ($villages->isEmpty()) {
            $this->command->warn('No active villages found. Creating standalone articles.');
            Article::factory()->count(10)->create();
            return;
        }

        // Create place-specific articles (70% of articles will be linked to places)
        foreach ($villages as $village) {
            $places = $village->places;

            if ($places->isNotEmpty()) {
                // Create 2-4 articles per village, most linked to places
                $articleCount = fake()->numberBetween(2, 4);

                for ($i = 0; $i < $articleCount; $i++) {
                    if ($i < $articleCount - 1) {
                        // Link to a random place in this village
                        $place = $places->random();
                        Article::factory()
                            ->forPlace($place)
                            ->create();

                        $this->command->info("  ✓ Created article for {$place->name} in {$village->name}");
                    } else {
                        // Create one village-level article (not linked to specific place)
                        Article::factory()
                            ->forVillage($village)
                            ->create([
                                'place_id' => null,
                            ]);

                        $this->command->info("  ✓ Created village-level article for {$village->name}");
                    }
                }
            } else {
                // Village has no places, create village-level articles
                Article::factory()
                    ->forVillage($village)
                    ->count(2)
                    ->create([
                        'place_id' => null,
                    ]);

                $this->command->info("  ✓ Created 2 village-level articles for {$village->name} (no places)");
            }
        }

        // Create some additional random articles
        Article::factory()
            ->count(5)
            ->create();

        // Create featured articles with specific content
        $featuredArticles = [
            [
                'title' => 'Panduan Lengkap Wisata Lombok Utara',
                'slug' => null,
                'content' => '<h2>Menjelajahi Keindahan Lombok Utara</h2>
                    <p>Lombok Utara menawarkan pesona alam yang tak terbantahkan dengan berbagai destinasi wisata yang memukau. Dari pantai-pantai eksotis hingga gunung yang menjulang tinggi, setiap sudut wilayah ini menyimpan keindahan yang siap memanjakan mata pengunjung.</p>

                    <h3>Destinasi Wajib Dikunjungi</h3>
                    <ul>
                        <li>Gili Trawangan - Surga bawah laut dengan kehidupan marina yang kaya</li>
                        <li>Pantai Senggigi - Pantai dengan sunset terindah di Lombok</li>
                        <li>Gunung Rinjani - Pendakian menantang dengan pemandangan spektakuler</li>
                        <li>Air Terjun Sendang Gile - Kesegaran air pegunungan yang menyegarkan</li>
                    </ul>

                    <p>Setiap destinasi memiliki keunikan tersendiri yang akan memberikan pengalaman tak terlupakan bagi setiap pengunjung. Jangan lupa untuk menghormati budaya lokal dan menjaga kelestarian alam selama berkunjung.</p>

                    <h3>Tips Perjalanan</h3>
                    <p>Rencanakan perjalanan Anda dengan baik, bawa perlengkapan yang cukup, dan selalu ikuti petunjuk guide lokal untuk pengalaman yang aman dan menyenangkan.</p>',
                'place_id' => null,
                'village_id' => $villages->first()->id,
            ],
            [
                'title' => 'Kuliner Khas Lombok yang Wajib Dicoba',
                'slug' => null,
                'content' => '<h2>Cita Rasa Autentik Lombok</h2>
                    <p>Lombok tidak hanya terkenal dengan keindahan alamnya, tetapi juga kekayaan kulinernya yang menggugah selera. Bumbu rempah yang khas dan cara memasak tradisional menghasilkan hidangan yang tak terlupakan.</p>

                    <h3>Makanan Khas yang Harus Dicoba</h3>
                    <ul>
                        <li>Ayam Taliwang - Ayam bakar dengan bumbu pedas khas Lombok</li>
                        <li>Plecing Kangkung - Kangkung dengan sambal tomat yang segar</li>
                        <li>Sate Bulayak - Sate dengan lontong khas berbentuk silinder</li>
                        <li>Bebalung - Sup tulang sapi dengan kuah bening yang gurih</li>
                        <li>Nasi Balap Puyung - Nasi dengan lauk beragam khas Lombok</li>
                    </ul>

                    <p>Setiap hidangan merefleksikan budaya dan tradisi masyarakat Sasak yang telah turun temurun. Rasanya yang autentik dan bumbu yang kaya membuat kuliner Lombok memiliki tempat istimewa di hati para penikmat makanan.</p>

                    <h3>Tempat Terbaik Mencicipi</h3>
                    <p>Kunjungi warung-warung lokal dan rumah makan tradisional untuk merasakan cita rasa yang paling autentik. Jangan ragu untuk bertanya pada penduduk lokal untuk rekomendasi tempat makan terbaik.</p>',
                'place_id' => null,
                'village_id' => $villages->random()->id,
            ],
        ];

        foreach ($featuredArticles as $articleData) {
            Article::create([
                'title' => $articleData['title'],
                'slug' => null,
                'content' => $articleData['content'],
                'cover_image_url' => 'https://picsum.photos/1200/600?random=' . fake()->numberBetween(300, 400),
                'place_id' => $articleData['place_id'],
                'village_id' => $articleData['village_id'],
            ]);

            $this->command->info("  ✓ Created featured article: {$articleData['title']}");
        }

        // Create some articles specifically for places with no existing articles
        $placesWithoutArticles = SmeTourismPlace::whereDoesntHave('articles')
            ->with('village')
            ->take(5)
            ->get();

        foreach ($placesWithoutArticles as $place) {
            Article::factory()
                ->forPlace($place)
                ->create();

            $this->command->info("  ✓ Created article for place without articles: {$place->name}");
        }

        $totalArticles = Article::count();
        $articlesWithPlaces = Article::whereNotNull('place_id')->count();
        $articlesWithVillages = Article::whereNotNull('village_id')->count();

        $this->command->info("✅ Article seeding completed!");
        $this->command->info("   Total articles: {$totalArticles}");
        $this->command->info("   Articles linked to places: {$articlesWithPlaces}");
        $this->command->info("   Articles linked to villages: {$articlesWithVillages}");
    }
}
