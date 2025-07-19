<?php

// app/Policies/PlacePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Place;

class PlacePolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->isSmeAdmin();
    }

    public function view(User $user, Place $place): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isVillageAdmin() && $user->village_id === $place->village_id) {
            return true;
        }

        if ($user->isCommunityAdmin() && $user->village_id === $place->village_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin();
    }

    public function update(User $user, Place $place): bool
    {
        return $this->view($user, $place);
    }

    public function delete(User $user, Place $place): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin();
    }
}
