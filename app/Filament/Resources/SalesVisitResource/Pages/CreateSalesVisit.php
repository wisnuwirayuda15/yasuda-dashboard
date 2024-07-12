<?php

namespace App\Filament\Resources\SalesVisitResource\Pages;

use App\Filament\Resources\SalesVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesVisit extends CreateRecord
{
    protected static string $resource = SalesVisitResource::class;

    protected function getRedirectUrl(): string
    {
      return $this->getResource()::getUrl('index');
    }
}
