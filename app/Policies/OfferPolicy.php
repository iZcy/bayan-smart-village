<?php

// app/Policies/OfferPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Offer;

class OfferPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All roles can view offers
    }

    public function view(User $user, Offer $offer): bool
    {
        return $user->canManageOffer($offer);
    }

    public function create(User $user): bool
    {
        return true; // All roles can create offers (within their scope)
    }

    public function update(User $user, Offer $offer): bool
    {
        return $user->canManageOffer($offer);
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $user->canManageOffer($offer);
    }
}
