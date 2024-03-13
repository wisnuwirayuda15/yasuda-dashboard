<?php

namespace App\Filament\Resources\BusAvailabilityResource\Pages;

use App\Filament\Resources\BusAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusAvailability extends EditRecord
{
    protected static string $resource = BusAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
