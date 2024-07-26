<?php

namespace App\Filament\Actions;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class ApproveBulkAction extends BulkAction
{
  public static function getDefaultName(): ?string
  {
    return 'Approve Semua';
  }

  protected function setUp(): void
  {
    parent::setUp();

    $this
      ->requiresConfirmation()
      ->deselectRecordsAfterCompletion()
      ->icon('heroicon-m-check')
      ->label($this->getName())
      ->color('success')
      ->before(function (Collection $records, BulkAction $action) {
        $user = auth()->user();

        $notApprovable = $records->some(fn(Model $record) =>
          !$record->canBeApprovedBy($user) ||
          !$record->isSubmitted() ||
          $record->isApprovalCompleted() ||
          $record->isDiscarded());

        if ($notApprovable) {
          Notification::make()
            ->danger()
            ->title('Failed')
            ->body('Anda tidak memiliki akses atau ada beberapa data yang tidak memenuhi syarat untuk melakukan aksi ini')
            ->send();

          $action->cancel();
        }
      })
      ->action(function (Collection $records, BulkAction $action) {
        $user = auth()->user();

        $records->each->approve(user: $user);

        Notification::make()
          ->success()
          ->title('Success')
          ->body('Semua record berhasil diapprove')
          ->send();
      });
  }
}
