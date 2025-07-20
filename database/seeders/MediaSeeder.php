<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Village;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding media files...');

        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        foreach ($villages as $village) {
            $this->command->info("Creating media for {$village->name}");

            // Create background videos for each context
            $contexts = ['home', 'places', 'products', 'articles', 'gallery'];

            foreach ($contexts as $context) {
                // Featured background video for each context
                Media::factory()
                    ->forVillage($village)
                    ->forContext($context)
                    ->video()
                    ->background()
                    ->create([
                        'title' => ucfirst($context) . ' Background Video',
                        'description' => "Background video for {$context} section in {$village->name}",
                    ]);

                // Featured ambient audio for each context
                Media::factory()
                    ->forVillage($village)
                    ->forContext($context)
                    ->audio()
                    ->ambient()
                    ->create([
                        'title' => ucfirst($context) . ' Ambient Audio',
                        'description' => "Ambient audio for {$context} section in {$village->name}",
                    ]);

                // Additional media for variety
                if ($context === 'home') {
                    // Extra home videos
                    Media::factory()
                        ->count(2)
                        ->forVillage($village)
                        ->forContext($context)
                        ->video()
                        ->create();

                    // Extra home audio
                    Media::factory()
                        ->count(2)
                        ->forVillage($village)
                        ->forContext($context)
                        ->audio()
                        ->create();
                } else {
                    // One additional media per other context
                    Media::factory()
                        ->forVillage($village)
                        ->forContext($context)
                        ->create();
                }
            }

            // Global media (available on all pages)
            Media::factory()
                ->count(3)
                ->forVillage($village)
                ->forContext('global')
                ->create();
        }

        // Create some media without village association (system-wide)
        Media::factory()
            ->count(5)
            ->forContext('global')
            ->create([
                'village_id' => null,
                'title' => 'System Global Media',
                'description' => 'Media available across all villages and pages',
            ]);

        $this->command->info('Media seeded successfully!');
        $this->displayMediaStatistics();
    }

    private function displayMediaStatistics(): void
    {
        $this->command->info("\n=== MEDIA STATISTICS ===");
        $this->command->info('Total media files: ' . Media::count());
        $this->command->info('Video files: ' . Media::where('type', 'video')->count());
        $this->command->info('Audio files: ' . Media::where('type', 'audio')->count());
        $this->command->info('Featured media: ' . Media::where('is_featured', true)->count());
        $this->command->info('Active media: ' . Media::where('is_active', true)->count());

        // Context breakdown
        $this->command->info('Context breakdown:');
        $contexts = ['home', 'places', 'products', 'articles', 'gallery', 'global'];
        foreach ($contexts as $context) {
            $count = Media::where('context', $context)->count();
            $this->command->info("  - {$context}: {$count}");
        }

        // Village breakdown
        $villageMedia = Media::whereNotNull('village_id')
            ->join('villages', 'media.village_id', '=', 'villages.id')
            ->selectRaw('villages.name, COUNT(*) as count')
            ->groupBy('villages.id', 'villages.name')
            ->get();

        if ($villageMedia->isNotEmpty()) {
            $this->command->info('Village breakdown:');
            foreach ($villageMedia as $vm) {
                $this->command->info("  - {$vm->name}: {$vm->count}");
            }
        }

        $this->command->info('========================\n');
    }
}
