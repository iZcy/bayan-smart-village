<?php

// app/Policies/ArticlePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Article;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All roles can view articles
    }

    public function view(User $user, Article $article): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isVillageAdmin() && $user->village_id === $article->village_id) {
            return true;
        }

        if ($user->isCommunityAdmin() && $user->community_id === $article->community_id) {
            return true;
        }

        if ($user->isSmeAdmin() && $user->sme_id === $article->sme_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true; // All roles can create articles
    }

    public function update(User $user, Article $article): bool
    {
        return $this->view($user, $article);
    }

    public function delete(User $user, Article $article): bool
    {
        return $this->view($user, $article);
    }
}
