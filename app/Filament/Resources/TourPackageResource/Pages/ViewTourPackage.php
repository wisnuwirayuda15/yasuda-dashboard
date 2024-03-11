<?php

namespace App\Filament\Resources\TourPackageResource\Pages;

use App\Filament\Resources\TourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTourPackage extends ViewRecord
{
    protected static string $resource = TourPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
