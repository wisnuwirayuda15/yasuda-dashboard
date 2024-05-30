<?php

namespace App\Filament\Resources\TourLeaderResource\Pages;

use App\Filament\Resources\TourLeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTourLeader extends EditRecord
{
    protected static string $resource = TourLeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
