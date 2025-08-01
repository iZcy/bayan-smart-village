<?php

// app/Filament/Resources/SmeResource/Pages/CreateSme.php
namespace App\Filament\Resources\SmeResource\Pages;

use App\Filament\Resources\SmeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSme extends CreateRecord
{
    protected static string $resource = SmeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure business_hours is properly structured for JSON storage
        if (isset($data['business_hours']) && is_array($data['business_hours'])) {
            // The form data comes in as nested arrays from the form fields
            // Keep it as is - Laravel will handle JSON conversion
        } else {
            // Initialize business hours with default values for all days if not set
            $data['business_hours'] = [
                'Sunday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Monday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Tuesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Wednesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Friday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Saturday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            ];
        }

        return $data;
    }
}
