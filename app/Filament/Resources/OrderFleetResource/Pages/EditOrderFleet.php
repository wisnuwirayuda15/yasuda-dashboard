<?php

namespace App\Filament\Resources\OrderFleetResource\Pages;

use App\Filament\Resources\OrderFleetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderFleet extends EditRecord
{
    protected static string $resource = OrderFleetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
