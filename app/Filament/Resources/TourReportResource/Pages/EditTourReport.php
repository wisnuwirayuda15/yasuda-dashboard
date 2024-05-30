<?php

namespace App\Filament\Resources\TourReportResource\Pages;

use App\Filament\Resources\TourReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTourReport extends EditRecord
{
  protected static string $resource = TourReportResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\ViewAction::make(),
      // Actions\DeleteAction::make(),
    ];
  }
}
