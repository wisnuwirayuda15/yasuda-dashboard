<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use App\Filament\Resources\ProfitLossResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfitLoss extends EditRecord
{
  protected static string $resource = ProfitLossResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\ViewAction::make(),
      // Actions\DeleteAction::make(),
    ];
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    // Prevent user change the invoice_id using javascript
    $data['invoice_id'] = $this->getRecord()->invoice->id;

    return $data;
  }
}
