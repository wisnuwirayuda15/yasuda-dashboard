<?php

namespace App\Filament\Resources\SalesVisitResource\Pages;

use App\Filament\Resources\SalesVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesVisits extends ListRecords
{
    protected static string $resource = SalesVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
