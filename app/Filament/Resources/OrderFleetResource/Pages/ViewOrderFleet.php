<?php

namespace App\Filament\Resources\OrderFleetResource\Pages;

use App\Filament\Resources\OrderFleetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderFleet extends ViewRecord
{
    protected static string $resource = OrderFleetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
