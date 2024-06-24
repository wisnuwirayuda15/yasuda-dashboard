<?php

namespace App\Filament\Resources\LoyaltyPointResource\Pages;

use App\Filament\Resources\LoyaltyPointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyPoint extends EditRecord
{
    protected static string $resource = LoyaltyPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
