<?php

// app/Filament/Resources/SmeResource/Pages/EditSme.php
namespace App\Filament\Resources\SmeResource\Pages;

use App\Filament\Resources\SmeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSme extends EditRecord
{
    protected static string $resource = SmeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        
        // Handle logo_url - get raw database value instead of accessor URL
        if ($record && $record->logo_url) {
            $data['logo_url'] = $record->getRawOriginal('logo_url');
        }

        // Initialize business hours with default values for all days if not set
        if (!isset($data['business_hours']) || empty($data['business_hours'])) {
            $data['business_hours'] = [
                'Sunday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Monday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Tuesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Wednesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Friday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'Saturday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            ];
        } else {
            // Ensure all days are present for existing records
            $orderedDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($orderedDays as $day) {
                if (!isset($data['business_hours'][$day])) {
                    $data['business_hours'][$day] = ['open' => '09:00', 'close' => '17:00', 'closed' => false];
                }
            }
        }

        return $data;
    }
}
