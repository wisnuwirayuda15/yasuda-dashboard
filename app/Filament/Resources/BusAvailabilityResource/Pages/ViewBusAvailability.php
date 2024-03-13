<?php

namespace App\Filament\Resources\BusAvailabilityResource\Pages;

use App\Filament\Resources\BusAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBusAvailability extends ViewRecord
{
    protected static string $resource = BusAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
