<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use App\Models\OrderFleet;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use EightyNine\Approvals\Models\ApprovableModel;

class CreateOrder extends CreateRecord
{
  protected static string $resource = OrderResource::class;

  protected function handleRecordCreation(array $data): Model
  {
    $model = static::getModel()::create($data);

    if (filled($data['order_fleet_ids'])) {
      $orderFleets = OrderFleet::find($data['order_fleet_ids']);

      $orderFleets->each->update(['order_id' => $model->id]);
    }

    instant_approval($model, $data);

    return $model;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
