<?php

// app/Policies/CategoryPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->isSmeAdmin();
    }

    public function view(User $user, Category $category): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isVillageAdmin() && $user->village_id === $category->village_id) {
            return true;
        }

        if ($user->isCommunityAdmin() && $user->village_id === $category->village_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin();
    }

    public function update(User $user, Category $category): bool
    {
        return $this->view($user, $category);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isSuperAdmin() || $user->isVillageAdmin();
    }
}
