<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use App\Filament\Resources\ProfitLossResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProfitLoss extends ViewRecord
{
    protected static string $resource = ProfitLossResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
