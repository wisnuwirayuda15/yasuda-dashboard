<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\OrderResource;
use App\Models\OrderFleet;

class EditOrder extends EditRecord
{
  protected static string $resource = OrderResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\ViewAction::make(),
      Actions\DeleteAction::make(),
    ];
  }
  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    if(isset($data['trip_date'])) {
      if (!$record->trip_date->isSameDay($data['trip_date'])) {
        OrderFleet::where('order_id', $record->id)->update(['order_id' => null]);
      }
    }

    $record->update($data);

    return $record;
  }
}
