<?php

namespace App\Filament\Resources\DestinationResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\DestinationResource;
use EightyNine\Approvals\Models\ApprovableModel;

class CreateDestination extends CreateRecord
{
  protected static string $resource = DestinationResource::class;

  protected function handleRecordCreation(array $data): Model
  {
    $model = static::getModel()::create($data);

    instant_approval($data, $model);

    return $model;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
