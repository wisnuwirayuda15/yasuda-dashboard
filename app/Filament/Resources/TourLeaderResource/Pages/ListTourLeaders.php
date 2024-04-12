<?php

namespace App\Filament\Resources\TourLeaderResource\Pages;

use App\Filament\Resources\TourLeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTourLeaders extends ListRecords
{
    protected static string $resource = TourLeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
