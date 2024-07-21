<?php

namespace App\Filament\Resources\FleetResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\FleetResource;
use Filament\Resources\Pages\CreateRecord;
use EightyNine\Approvals\Models\ApprovableModel;

class CreateFleet extends CreateRecord
{
  protected static string $resource = FleetResource::class;

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
