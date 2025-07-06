<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\SmeTourismPlace;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        // Create 20 articles, some linked to places, some standalone
        Article::factory()
            ->count(15)
            ->create();

        // Create 5 articles specifically without place links
        Article::factory()
            ->withoutPlace()
            ->count(5)
            ->create();

        // Create featured articles
        $featuredArticles = [
            [
                'title' => 'Panduan Lengkap Wisata Lombok Utara',
                'content' => '<h2>Menjelajahi Keindahan Lombok Utara</h2>
                    <p>Lombok Utara menawarkan pesona alam yang tak terbantahkan dengan berbagai destinasi wisata yang memukau. Dari pantai-pantai eksotis hingga gunung yang menjulang tinggi, setiap sudut wilayah ini menyimpan keindahan yang siap memanjakan mata pengunjung.</p>

                    <h3>Destinasi Wajib Dikunjungi</h3>
                    <ul>
                        <li>Gili Trawangan - Surga bawah laut dengan kehidupan marina yang kaya</li>
                        <li>Pantai Senggigi - Pantai dengan sunset terindah di Lombok</li>
                        <li>Gunung Rinjani - Pendakian menantang dengan pemandangan spektakuler</li>
                        <li>Air Terjun Sendang Gile - Kesegaran air pegunungan yang menyegarkan</li>
                    </ul>

                    <p>Setiap destinasi memiliki keunikan tersendiri yang akan memberikan pengalaman tak terlupakan bagi setiap pengunjung.</p>',
                'place_id' => null,
            ],
            [
                'title' => 'Kuliner Khas Lombok yang Wajib Dicoba',
                'content' => '<h2>Cita Rasa Autentik Lombok</h2>
                    <p>Lombok tidak hanya terkenal dengan keindahan alamnya, tetapi juga kekayaan kulinernya yang menggugah selera. Bumbu rempah yang khas dan cara memasak tradisional menghasilkan hidangan yang tak terlupakan.</p>

                    <h3>Makanan Khas yang Harus Dicoba</h3>
                    <ul>
                        <li>Ayam Taliwang - Ayam bakar dengan bumbu pedas khas Lombok</li>
                        <li>Plecing Kangkung - Kangkung dengan sambal tomat yang segar</li>
                        <li>Sate Bulayak - Sate dengan lontong khas berbentuk silinder</li>
                        <li>Bebalung - Sup tulang sapi dengan kuah bening yang gurih</li>
                    </ul>

                    <p>Setiap hidangan merefleksikan budaya dan tradisi masyarakat Sasak yang telah turun temurun.</p>',
                'place_id' => null,
            ],
        ];

        foreach ($featuredArticles as $articleData) {
            Article::create([
                'title' => $articleData['title'],
                'content' => $articleData['content'],
                'cover_image_url' => 'https://picsum.photos/1200/600?random=' . fake()->numberBetween(300, 400),
                'place_id' => $articleData['place_id'],
            ]);
        }
    }
}
