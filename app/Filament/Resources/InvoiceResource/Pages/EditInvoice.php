<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
  protected static string $resource = InvoiceResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\ViewAction::make(),
      Actions\DeleteAction::make(),
      Actions\ForceDeleteAction::make(),
      Actions\RestoreAction::make(),
    ];
  }

  protected function mutateFormDataBeforeFill(array $data): array
  {
    // $data['order_id']; 
    
    // dd($data);

    return $data;
  }
}
