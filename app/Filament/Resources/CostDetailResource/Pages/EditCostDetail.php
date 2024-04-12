<?php

namespace App\Filament\Resources\CostDetailResource\Pages;

use App\Filament\Resources\CostDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCostDetail extends EditRecord
{
    protected static string $resource = CostDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
