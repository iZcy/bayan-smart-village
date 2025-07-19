<?php

// app/Policies/VillagePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Village;

class VillagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin();
    }

    public function view(User $user, Village $village): bool
    {
        return $user->canManageVillage($village);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Village $village): bool
    {
        return $user->canManageVillage($village);
    }

    public function delete(User $user, Village $village): bool
    {
        return $user->isSuperAdmin();
    }
}
