<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Auth\Access\AuthorizationException;

class EditUser extends EditRecord
{
  protected static string $resource = UserResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\ViewAction::make(),
      // Actions\DeleteAction::make()
      //   ->requiresPasswordConfirmation(),
    ];
  }

  protected function mutateFormDataBeforeFill(array $data): array
  {
    if ($data['id'] === auth()->id()) {
      throw new AuthorizationException();
    }

    return $data;
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
