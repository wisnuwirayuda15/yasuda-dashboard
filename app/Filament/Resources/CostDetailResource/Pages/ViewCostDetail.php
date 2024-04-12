<?php

namespace App\Filament\Resources\CostDetailResource\Pages;

use App\Filament\Resources\CostDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCostDetail extends ViewRecord
{
    protected static string $resource = CostDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
