<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use App\Models\OrderFleet;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
  protected static string $resource = OrderResource::class;

  protected function handleRecordCreation(array $data): Model
  {
    $record = static::getModel()::create($data);

    OrderFleet::findOrFail($data['order_fleets_id'])->update(['order_id' => $record->id]);

    return $record;
  }
}
