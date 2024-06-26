<?php

namespace App\Filament\Resources\SalesVisitResource\Pages;

use App\Filament\Resources\SalesVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesVisit extends EditRecord
{
  protected static string $resource = SalesVisitResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\ViewAction::make(),
      Actions\DeleteAction::make(),
    ];
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    $data['employee_id'] ??= null;

    return $data;
  }
}
