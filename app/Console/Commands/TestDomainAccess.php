<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Village;
use Illuminate\Console\Command;

class TestDomainAccess extends Command
{
    protected $signature = 'test:domain-access';
    protected $description = 'Test domain access logic for different user types';

    public function handle()
    {
        $this->info('Testing Domain Access Logic');
        $this->info('================================');

        // Get sample users of different types
        $superAdmin = User::where('role', User::ROLE_SUPER_ADMIN)->first();
        $villageAdmin = User::where('role', User::ROLE_VILLAGE_ADMIN)->first();
        $communityAdmin = User::where('role', User::ROLE_COMMUNITY_ADMIN)->first();
        $smeAdmin = User::where('role', User::ROLE_SME_ADMIN)->first();

        // Get a sample village
        $village = Village::first();

        if (!$superAdmin || !$villageAdmin || !$communityAdmin || !$smeAdmin || !$village) {
            $this->error('Missing required test data. Please ensure you have users of all types and at least one village.');
            return;
        }

        $this->info('Test Users:');
        $this->info("Super Admin: {$superAdmin->email}");
        $this->info("Village Admin: {$villageAdmin->email} (Village: " . ($villageAdmin->village->name ?? 'N/A') . ")");
        $this->info("Community Admin: {$communityAdmin->email} (Community: " . ($communityAdmin->community->name ?? 'N/A') . ")");
        $this->info("SME Admin: {$smeAdmin->email} (SME: " . ($smeAdmin->sme->name ?? 'N/A') . ")");
        $this->info("Test Village: {$village->name}");
        $this->newLine();

        // Test main domain access
        $this->info('Main Domain Access:');
        $this->info("Super Admin can access main domain: " . ($superAdmin->canAccessMainDomain() ? 'YES' : 'NO'));
        $this->info("Village Admin can access main domain: " . ($villageAdmin->canAccessMainDomain() ? 'YES' : 'NO'));
        $this->info("Community Admin can access main domain: " . ($communityAdmin->canAccessMainDomain() ? 'YES' : 'NO'));
        $this->info("SME Admin can access main domain: " . ($smeAdmin->canAccessMainDomain() ? 'YES' : 'NO'));
        $this->newLine();

        // Test village domain access
        $this->info("Village Domain Access ({$village->name}):");
        $this->info("Super Admin can access village domain: " . ($superAdmin->canAccessVillageDomain($village) ? 'YES' : 'NO'));
        $this->info("Village Admin can access this village domain: " . ($villageAdmin->canAccessVillageDomain($village) ? 'YES' : 'NO'));
        $this->info("Community Admin can access this village domain: " . ($communityAdmin->canAccessVillageDomain($village) ? 'YES' : 'NO'));
        $this->info("SME Admin can access this village domain: " . ($smeAdmin->canAccessVillageDomain($village) ? 'YES' : 'NO'));
        $this->newLine();

        // Test accessible villages
        $this->info('Accessible Village Domains:');
        $this->info("Super Admin accessible villages: " . $superAdmin->getAccessibleDomainVillages()->pluck('name')->implode(', ') ?: 'None');
        $this->info("Village Admin accessible villages: " . $villageAdmin->getAccessibleDomainVillages()->pluck('name')->implode(', ') ?: 'None');
        $this->info("Community Admin accessible villages: " . $communityAdmin->getAccessibleDomainVillages()->pluck('name')->implode(', ') ?: 'None');
        $this->info("SME Admin accessible villages: " . $smeAdmin->getAccessibleDomainVillages()->pluck('name')->implode(', ') ?: 'None');

        $this->newLine();
        $this->info('Domain access logic test completed!');
    }
}