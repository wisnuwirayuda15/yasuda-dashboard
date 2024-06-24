<?php

namespace App\Filament\Resources\SalesVisitResource\Pages;

use App\Filament\Resources\SalesVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesVisit extends ViewRecord
{
    protected static string $resource = SalesVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
