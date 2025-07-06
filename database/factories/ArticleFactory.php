<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\SmeTourismPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $titles = [
            'Menikmati Keindahan Pantai Senggigi di Sore Hari',
            'Kuliner Khas Lombok yang Wajib Dicoba',
            'Pendakian Gunung Rinjani: Tips dan Trik',
            'Menjelajahi Gili Trawangan dengan Budget Minim',
            'Warung Tradisional dengan Cita Rasa Autentik',
            'Destinasi Wisata Tersembunyi di Lombok Utara',
            'Pengalaman Berbelanja di Pasar Tradisional',
            'Aktivitas Seru di Pantai Kuta Lombok',
            'Menikmati Kopi Lokal dengan Pemandangan Menakjubkan',
            'Desa Wisata Sasak: Mengenal Budaya Lokal'
        ];

        $content = $this->generateRichContent();

        return [
            'title' => $this->faker->randomElement($titles),
            'content' => $content,
            'cover_image_url' => $this->faker->boolean(70) ?
                'https://picsum.photos/1200/600?random=' . $this->faker->numberBetween(1, 1000) : null,
            'place_id' => $this->faker->boolean(60) ?
                SmeTourismPlace::inRandomOrder()->first()?->id : null,
        ];
    }

    private function generateRichContent(): string
    {
        $paragraphs = [
            '<p>Lombok Utara menawarkan pesona alam yang tak terbantahkan dengan berbagai destinasi wisata yang memukau. Dari pantai-pantai eksotis hingga gunung yang menjulang tinggi, setiap sudut wilayah ini menyimpan keindahan yang siap memanjakan mata pengunjung.</p>',

            '<p>Selain keindahan alamnya, Lombok Utara juga kaya akan kuliner tradisional yang menggugah selera. Warung-warung lokal menyajikan hidangan khas dengan bumbu rempah yang autentik, memberikan pengalaman gastronomi yang tak terlupakan.</p>',

            '<p>Masyarakat lokal yang ramah dan budaya Sasak yang masih terjaga menjadi daya tarik tersendiri. Wisatawan dapat belajar langsung tentang tradisi dan kehidupan sehari-hari penduduk setempat.</p>',

            '<h3>Aktivitas yang Dapat Dilakukan</h3>',
            '<ul>
                <li>Snorkeling dan diving di perairan jernih</li>
                <li>Trekking dan hiking di jalur-jalur alam</li>
                <li>Belajar membuat kerajinan tradisional</li>
                <li>Menikmati sunset di spot-spot terbaik</li>
            </ul>',

            '<p>Bagi para pecinta fotografi, tempat ini menawarkan berbagai spot foto Instagram-worthy yang akan membuat feed media sosial Anda semakin menarik. Setiap sudut memberikan background yang sempurna untuk mengabadikan momen berharga.</p>',

            '<h3>Tips Berkunjung</h3>',
            '<p>Waktu terbaik untuk berkunjung adalah pada musim kemarau, sekitar bulan April hingga Oktober. Pastikan untuk membawa perlengkapan yang sesuai dan menghormati budaya serta aturan setempat.</p>'
        ];

        $selectedParagraphs = $this->faker->randomElements($paragraphs, $this->faker->numberBetween(3, 6));
        return implode('', $selectedParagraphs);
    }

    public function withoutPlace(): static
    {
        return $this->state(fn(array $attributes) => [
            'place_id' => null,
        ]);
    }

    public function withPlace(): static
    {
        return $this->state(fn(array $attributes) => [
            'place_id' => SmeTourismPlace::factory(),
        ]);
    }
}
