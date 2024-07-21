<?php

namespace App\Filament\Resources\OrderFleetResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\OrderFleetResource;
use EightyNine\Approvals\Models\ApprovableModel;

class CreateOrderFleet extends CreateRecord
{
  protected static string $resource = OrderFleetResource::class;

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
