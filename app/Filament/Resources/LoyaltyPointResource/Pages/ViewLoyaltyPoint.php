<?php

namespace App\Filament\Resources\LoyaltyPointResource\Pages;

use App\Filament\Resources\LoyaltyPointResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLoyaltyPoint extends ViewRecord
{
    protected static string $resource = LoyaltyPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
