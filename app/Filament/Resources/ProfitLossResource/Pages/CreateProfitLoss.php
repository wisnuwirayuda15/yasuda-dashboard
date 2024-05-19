<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Route;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\ProfitLossResource;

class CreateProfitLoss extends CreateRecord
{
  protected static string $resource = ProfitLossResource::class;

  public function getTitle(): string|Htmlable
  {
    $invoice = request('invoice');

    if (blank($invoice) && Route::current()->getName() == 'livewire.update') {
      $parameters = getUrlQueryParameters(url()->previous());
      $invoice = $parameters['invoice'];
    }

    return 'Create Profit & Loss Analysis for ' . $invoice;
  }
}
