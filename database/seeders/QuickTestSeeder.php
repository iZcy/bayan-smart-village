<?php

namespace Database\Seeders;

use App\Models\Village;
use App\Models\Community;
use App\Models\Place;
use App\Models\Category;
use App\Models\Sme;
use App\Models\Offer;
use App\Models\OfferTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QuickTestSeeder extends Seeder
{
    /**
     * Quick seeder for testing purposes.
     * Creates minimal data to test the application.
     */
    public function run(): void
    {
        $this->command->info('Running quick test seeding...');

        // Create 1 village
        $village = Village::factory()->active()->create([
            'name' => 'Desa Test',
            'description' => 'Desa untuk testing aplikasi Smart Village'
        ]);

        // Create 2 communities
        $community1 = Community::factory()->forVillage($village)->active()->create([
            'name' => 'Komunitas Kerajinan'
        ]);

        $community2 = Community::factory()->forVillage($village)->active()->create([
            'name' => 'Komunitas Kuliner'
        ]);

        // Create 2 places
        $place1 = Place::factory()->forVillage($village)->tourism()->create([
            'name' => 'Wisata Alam Test'
        ]);

        $place2 = Place::factory()->forVillage($village)->historical()->create([
            'name' => 'Situs Bersejarah Test'
        ]);

        // Create categories
        $categoryProduct = Category::factory()->forVillage($village)->product()->create([
            'name' => 'Kerajinan Tangan'
        ]);

        $categoryService = Category::factory()->forVillage($village)->service()->create([
            'name' => 'Jasa Wisata'
        ]);

        // Create SMEs
        $sme1 = Sme::factory()->forCommunity($community1)->product()->verified()->create([
            'name' => 'Kerajinan Bambu Berkah'
        ]);

        $sme2 = Sme::factory()->forCommunity($community2)->service()->verified()->create([
            'name' => 'Warung Makan Enak'
        ]);

        // Create some tags
        $tags = collect([
            OfferTag::factory()->create(['name' => 'Handmade', 'usage_count' => 5]),
            OfferTag::factory()->create(['name' => 'Bambu', 'usage_count' => 3]),
            OfferTag::factory()->create(['name' => 'Lokal', 'usage_count' => 8]),
        ]);

        // Create offers
        $offer1 = Offer::factory()->forSme($sme1)->inCategory($categoryProduct)->create([
            'name' => 'Tas Anyaman Bambu Premium'
        ]);

        $offer2 = Offer::factory()->forSme($sme2)->inCategory($categoryService)->create([
            'name' => 'Paket Makan Siang Tradisional'
        ]);

        // Attach tags to offers
        $offer1->tags()->attach($tags->pluck('id'));
        $offer2->tags()->attach($tags->random(2)->pluck('id'));

        // Create users
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Village Admin',
            'email' => 'village@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_VILLAGE_ADMIN,
            'village_id' => $village->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Community Admin',
            'email' => 'community@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_COMMUNITY_ADMIN,
            'village_id' => $village->id,
            'community_id' => $community1->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        User::create([
            'name' => 'SME Admin',
            'email' => 'sme@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SME_ADMIN,
            'village_id' => $village->id,
            'community_id' => $community1->id,
            'sme_id' => $sme1->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->command->info('Quick test seeding completed!');
        $this->command->info("\n=== TEST DATA SUMMARY ===");
        $this->command->info("Village: {$village->name}");
        $this->command->info("Communities: {$community1->name}, {$community2->name}");
        $this->command->info("Places: {$place1->name}, {$place2->name}");
        $this->command->info("SMEs: {$sme1->name}, {$sme2->name}");
        $this->command->info("Offers: {$offer1->name}, {$offer2->name}");
        $this->command->info("\n=== TEST USERS ===");
        $this->command->info("Super Admin: admin@test.com / password");
        $this->command->info("Village Admin: village@test.com / password");
        $this->command->info("Community Admin: community@test.com / password");
        $this->command->info("SME Admin: sme@test.com / password");
        $this->command->info("========================\n");
    }
}
