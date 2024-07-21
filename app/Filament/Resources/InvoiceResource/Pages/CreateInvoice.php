<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\InvoiceResource;
use EightyNine\Approvals\Models\ApprovableModel;

class CreateInvoice extends CreateRecord
{
  protected static string $resource = InvoiceResource::class;

  protected static bool $canCreateAnother = false;

  protected function handleRecordCreation(array $data): Model
  {
    $model = static::getModel()::create($data);

    instant_approval($model, $data);

    return $model;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
