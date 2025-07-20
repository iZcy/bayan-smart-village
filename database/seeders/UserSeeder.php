<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@kecamatanbayan.id',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create sample village admin if villages exist
        $sampleVillage = Village::first();
        if ($sampleVillage) {
            User::create([
                'name' => 'Village Admin - ' . $sampleVillage->name,
                'email' => 'village@' . str_replace(' ', '', strtolower($sampleVillage->name)) . '.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_VILLAGE_ADMIN,
                'village_id' => $sampleVillage->id,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            // Create sample community admin if communities exist
            $sampleCommunity = Community::where('village_id', $sampleVillage->id)->first();
            if ($sampleCommunity) {
                User::create([
                    'name' => 'Community Admin - ' . $sampleCommunity->name,
                    'email' => 'community@' . str_replace(' ', '', strtolower($sampleCommunity->name)) . '.com',
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_COMMUNITY_ADMIN,
                    'village_id' => $sampleVillage->id,
                    'community_id' => $sampleCommunity->id,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);

                // Create sample SME admin if SMEs exist
                $sampleSme = Sme::where('community_id', $sampleCommunity->id)->first();
                if ($sampleSme) {
                    User::create([
                        'name' => 'SME Admin - ' . $sampleSme->name,
                        'email' => 'sme@' . str_replace(' ', '', strtolower($sampleSme->name)) . '.com',
                        'password' => Hash::make('password'),
                        'role' => User::ROLE_SME_ADMIN,
                        'village_id' => $sampleVillage->id,
                        'community_id' => $sampleCommunity->id,
                        'sme_id' => $sampleSme->id,
                        'email_verified_at' => now(),
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Super Admin: admin@kecamatanbayan.id / password');

        if ($sampleVillage) {
            $this->command->info('Village Admin: village@' . str_replace(' ', '', strtolower($sampleVillage->name)) . '.com / password');
        }

        $this->command->info('Community Admin: community@' . str_replace(' ', '', strtolower($sampleCommunity->name)) . '.com / password');

        if ($sampleSme) {
            $this->command->info('SME Admin: sme@' . str_replace(' ', '', strtolower($sampleSme->name)) . '.com / password');
        }

        $this->command->info('All users have been created with the password "password". Please change them after login.');
        $this->command->info('You can create more users using the UserFactory or manually in the database.');
    }
}
