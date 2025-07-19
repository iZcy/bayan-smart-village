<?php

// app/Policies/SmePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Sme;

class SmePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All roles can view SMEs
    }

    public function view(User $user, Sme $sme): bool
    {
        return $user->canManageSme($sme);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin() || $user->isCommunityAdmin();
    }

    public function update(User $user, Sme $sme): bool
    {
        return $user->canManageSme($sme);
    }

    public function delete(User $user, Sme $sme): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin() || $user->isCommunityAdmin();
    }
}
