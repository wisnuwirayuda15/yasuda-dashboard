<?php

namespace App\Filament\Resources\TourTemplateResource\Pages;

use App\Filament\Resources\TourTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTourTemplates extends ManageRecords
{
    protected static string $resource = TourTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
