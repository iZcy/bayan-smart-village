<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user first
        $this->createAdminUser();

        // Then seed all the tourism data
        $this->call([
            CategorySeeder::class,
            SmeTourismPlaceSeeder::class,
            ArticleSeeder::class,
            ImageSeeder::class,
            ExternalLinkSeeder::class,
        ]);

        $this->command->info('🎉 Bayan Smart Village database seeded successfully!');
        $this->command->info('📧 Admin email: admin@bayansmart.com');
        $this->command->info('🔑 Admin password: password');
    }

    private function createAdminUser(): void
    {
        // Check if admin user already exists
        $existingUser = User::where('email', 'admin@bayansmart.com')->first();

        if ($existingUser) {
            $this->command->info('⚠️  Admin user already exists. Skipping user creation.');
            return;
        }

        // Create main admin user
        User::create([
            'name' => 'Bayan Smart Village Admin',
            'email' => 'admin@bayansmart.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Create additional demo users
        User::create([
            'name' => 'KKN Student',
            'email' => 'kkn@ugm.ac.id',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Tourism Manager',
            'email' => 'tourism@bayansmart.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $this->command->info('✅ Admin users created successfully!');
    }
}
