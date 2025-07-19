<?php

// app/Policies/CommunityPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Community;

class CommunityPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->isSmeAdmin();
    }

    public function view(User $user, Community $community): bool
    {
        return $user->canManageCommunity($community);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin();
    }

    public function update(User $user, Community $community): bool
    {
        return $user->canManageCommunity($community);
    }

    public function delete(User $user, Community $community): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin();
    }
}
