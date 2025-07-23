<?php

// app/Filament/Resources/OfferResource/Pages/CreateOffer.php

namespace App\Filament\Resources\OfferResource\Pages;

use App\Filament\Resources\OfferResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOffer extends CreateRecord
{
    protected static string $resource = OfferResource::class;

    protected function mutateRelationshipDataBeforeCreate(string $relationshipName, array $data, string $recordKey): array
    {
        if ($relationshipName === 'additionalImages') {
            // Set sort_order based on the array index (additional images start from 1)
            $data['sort_order'] = intval($recordKey) + 1;

            // Additional images are never primary (primary image is handled separately)
            $data['is_primary'] = false;
        }

        if ($relationshipName === 'ecommerceLinks') {
            // Set sort_order for e-commerce links
            $data['sort_order'] = intval($recordKey);
        }

        return $data;
    }
}
