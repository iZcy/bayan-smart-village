<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['video', 'audio']);
        $context = $this->faker->randomElement(['home', 'places', 'products', 'articles', 'gallery']);

        $videoTitles = [
            'home' => [
                'Village Life Documentary',
                'Cultural Heritage Overview',
                'Daily Life in the Village',
                'Traditional Ceremonies',
                'Community Activities',
            ],
            'places' => [
                'Scenic Beauty Tour',
                'Historical Site Documentation',
                'Natural Landscape Views',
                'Architectural Highlights',
                'Tourist Destination Guide',
            ],
            'products' => [
                'Craft Making Process',
                'Product Showcase',
                'Artisan at Work',
                'Traditional Techniques',
                'Behind the Scenes',
            ],
            'articles' => [
                'Story Behind the Article',
                'Interview Footage',
                'Event Documentation',
                'Cultural Explanation',
                'Historical Context',
            ],
            'gallery' => [
                'Photo Story Video',
                'Time-lapse Creation',
                'Gallery Slideshow',
                'Visual Journey',
                'Artistic Documentation',
            ],
        ];

        $audioTitles = [
            'home' => [
                'Village Ambient Sounds',
                'Traditional Music',
                'Nature Sounds',
                'Community Chants',
                'Cultural Songs',
            ],
            'places' => [
                'Nature Audio Guide',
                'Historical Narration',
                'Ambient Location Sounds',
                'Tourist Audio Guide',
                'Environmental Sounds',
            ],
            'products' => [
                'Crafting Process Audio',
                'Product Description',
                'Artisan Interview',
                'Workshop Sounds',
                'Production Ambience',
            ],
            'articles' => [
                'Article Narration',
                'Interview Audio',
                'Story Reading',
                'Background Music',
                'Cultural Commentary',
            ],
            'gallery' => [
                'Gallery Ambient Music',
                'Photo Description Audio',
                'Artistic Commentary',
                'Background Soundtrack',
                'Cultural Music',
            ],
        ];

        $titles = $type === 'video' ? $videoTitles : $audioTitles;
        $title = $this->faker->randomElement($titles[$context]);

        // Dummy file URLs (using existing paths from your code)
        $videoUrls = [
            '/video/videobackground.mp4',
            '/video/village-life.mp4',
            '/video/cultural-dance.mp4',
            '/video/traditional-craft.mp4',
            '/video/nature-scenes.mp4',
        ];

        $audioUrls = [
            '/audio/sasakbacksong.mp3',
            '/audio/village-nature.mp3',
            '/audio/traditional-music.mp3',
            '/audio/ambient-sounds.mp3',
            '/audio/cultural-songs.mp3',
        ];

        $fileUrl = $type === 'video'
            ? $this->faker->randomElement($videoUrls)
            : $this->faker->randomElement($audioUrls);

        $mimeType = $type === 'video'
            ? 'video/mp4'
            : 'audio/mpeg';

        return [
            'village_id' => $this->faker->optional(0.8)->randomElement(Village::pluck('id')->toArray() ?: [Village::factory()->create()->id]),
            'community_id' => $this->faker->optional(0.3)->randomElement(Community::pluck('id')->toArray() ?: [null]),
            'sme_id' => $this->faker->optional(0.2)->randomElement(Sme::pluck('id')->toArray() ?: [null]),
            'place_id' => $this->faker->optional(0.3)->randomElement(Place::pluck('id')->toArray() ?: [null]),
            'title' => $title,
            'description' => $this->faker->paragraph(2),
            'type' => $type,
            'context' => $context,
            'file_url' => $fileUrl,
            'thumbnail_url' => $type === 'video' ? $this->faker->optional(0.7)->imageUrl(800, 450) : null,
            'duration' => $this->faker->numberBetween(30, 300), // 30 seconds to 5 minutes
            'mime_type' => $mimeType,
            'file_size' => $this->faker->numberBetween(1048576, 52428800), // 1MB to 50MB
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
            'autoplay' => $type === 'video' ? $this->faker->boolean(30) : $this->faker->boolean(50),
            'loop' => $this->faker->boolean(40),
            'muted' => $type === 'video' ? true : false, // Videos default to muted
            'volume' => $this->faker->randomFloat(2, 0.1, 0.8), // Volume between 0.1 and 0.8
            'sort_order' => $this->faker->numberBetween(0, 100),
            'settings' => [
                'fade_in' => $this->faker->boolean(50),
                'fade_out' => $this->faker->boolean(50),
                'crossfade_duration' => $this->faker->numberBetween(1, 5),
            ],
        ];
    }

    /**
     * Indicate that the media is for a specific village.
     */
    public function forVillage(Village $village): static
    {
        return $this->state(fn(array $attributes) => [
            'village_id' => $village->id,
        ]);
    }

    /**
     * Indicate that the media is for a specific context.
     */
    public function forContext(string $context): static
    {
        return $this->state(fn(array $attributes) => [
            'context' => $context,
        ]);
    }

    /**
     * Video media type.
     */
    public function video(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'video',
            'mime_type' => 'video/mp4',
            'file_url' => $this->faker->randomElement([
                '/video/videobackground.mp4',
                '/video/village-life.mp4',
                '/video/cultural-dance.mp4',
                '/video/traditional-craft.mp4',
                '/video/nature-scenes.mp4',
            ]),
            'thumbnail_url' => 'https://picsum.photos/800/450?random=' . $this->faker->numberBetween(1, 1000),
            'muted' => true,
        ]);
    }

    /**
     * Audio media type.
     */
    public function audio(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'audio',
            'mime_type' => 'audio/mpeg',
            'file_url' => $this->faker->randomElement([
                '/audio/sasakbacksong.mp3',
                '/audio/village-nature.mp3',
                '/audio/traditional-music.mp3',
                '/audio/ambient-sounds.mp3',
                '/audio/cultural-songs.mp3',
            ]),
            'thumbnail_url' => null,
            'muted' => false,
        ]);
    }

    /**
     * Featured media.
     */
    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Background media (autoplay, loop).
     */
    public function background(): static
    {
        return $this->state(fn(array $attributes) => [
            'autoplay' => true,
            'loop' => true,
            'muted' => true,
            'volume' => 0.2,
            'is_featured' => true,
        ]);
    }

    /**
     * Ambient audio.
     */
    public function ambient(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'audio',
            'autoplay' => true,
            'loop' => true,
            'volume' => 0.3,
            'settings' => [
                'fade_in' => true,
                'fade_out' => true,
                'crossfade_duration' => 3,
            ],
        ]);
    }
}
