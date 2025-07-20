<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // Check if we want to run individual seeders or complete seeding
        $runComplete = $this->command->confirm('Run complete seeding? (Alternative: run individual seeders)', true);

        if ($runComplete) {
            $this->call([
                CompleteSeeder::class,
                UserSeeder::class, // Create users after all data is seeded
            ]);
        } else {
            $this->call([
                VillageSeeder::class,
                CommunitySeeder::class,
                CategorySeeder::class,
                PlaceSeeder::class,
                SmeSeeder::class,
                OfferTagSeeder::class,
                OfferSeeder::class,
                ArticleSeeder::class,
                ExternalLinkSeeder::class,
                ImageSeeder::class,
                UserSeeder::class,
            ]);
        }

        $this->command->info('Database seeding completed successfully!');
    }
}
