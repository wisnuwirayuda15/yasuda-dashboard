<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use App\Models\Invoice;
use Illuminate\Support\Facades\Route;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\ProfitLossResource;
use App\Models\ProfitLoss;

class CreateProfitLoss extends CreateRecord
{
  protected static string $resource = ProfitLossResource::class;

  protected static bool $canCreateAnother = false;

  public function beforeFill(): void
  {
    $inv = Invoice::where('code', request('invoice'))->firstOrFail();

    $pnl = ProfitLoss::withoutGlobalScopes()->where('invoice_id', $inv->id)->first();

    // Each invoice should only has one profit & loss
    (bool) $pnl ? redirect(ProfitLossResource::getUrl('view', ['record' => $pnl->id])) : null;
  }
}
