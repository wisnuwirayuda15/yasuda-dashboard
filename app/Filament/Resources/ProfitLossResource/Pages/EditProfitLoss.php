<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use App\Filament\Resources\ProfitLossResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfitLoss extends EditRecord
{
  protected static string $resource = ProfitLossResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\ViewAction::make(),
      // Actions\DeleteAction::make(),
    ];
  }
}
