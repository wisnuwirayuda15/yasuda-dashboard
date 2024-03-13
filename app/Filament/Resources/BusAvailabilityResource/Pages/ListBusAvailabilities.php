<?php

namespace App\Filament\Resources\BusAvailabilityResource\Pages;

use App\Filament\Resources\BusAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusAvailabilities extends ListRecords
{
    protected static string $resource = BusAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
