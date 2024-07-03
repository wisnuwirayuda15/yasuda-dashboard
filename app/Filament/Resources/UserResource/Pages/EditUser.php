<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
  protected static string $resource = UserResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\ViewAction::make(),
      Actions\DeleteAction::make()
        ->slideOver(false)
        ->requiresPasswordConfirmation(),
    ];
  }

  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    if ($data['email'] !== $record->email) {
      $record->unverify();
    }

    $record->update($data);

    return $record;
  }
}
