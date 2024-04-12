<?php

namespace App\Filament\Resources\TourLeaderResource\Pages;

use App\Filament\Resources\TourLeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTourLeader extends ViewRecord
{
    protected static string $resource = TourLeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
