<?php

namespace App\Filament\Actions;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class BulkSubmitAction extends BulkAction
{
  public static function getDefaultName(): ?string
  {
    return 'Submit Semua';
  }

  protected function setUp(): void
  {
    parent::setUp();

    $this
      ->requiresConfirmation()
      ->deselectRecordsAfterCompletion()
      ->icon('heroicon-m-arrow-right-circle')
      ->label($this->getName())
      ->color('info')
      ->action(function (Collection $records, BulkAction $action) {
        $user = auth()->user();

        $notSubmitable = $records->some(fn(Model $record) =>
          $record->isSubmitted() ||
          $record->approvalStatus->creator_id !== $user->id);

        if ($notSubmitable) {
          Notification::make()
            ->danger()
            ->title('Failed')
            ->body('Anda tidak memiliki akses atau ada beberapa data yang tidak memenuhi syarat untuk melakukan aksi ini')
            ->send();

          $action->cancel();
        }

        $records->each->submit(user: $user);

        Notification::make()
          ->success()
          ->title('Success')
          ->body('Semua record berhasil disubmit')
          ->send();
      });
  }
}
