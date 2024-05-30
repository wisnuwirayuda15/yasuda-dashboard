<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use App\Models\Invoice;
use Illuminate\Support\Facades\Route;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\ProfitLossResource;

class CreateProfitLoss extends CreateRecord
{
  protected static string $resource = ProfitLossResource::class;

  protected static bool $canCreateAnother = false;

  public function beforeFill(): void
  {
    $pnl = Invoice::where('code', request('invoice'))->firstOrFail()->profitLoss;

    // Each invoice should only has one profit & loss
    $pnl ? redirect(ProfitLossResource::getUrl('view', ['record' => $pnl->id])) : null;
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $pnl = Invoice::findOrFail($data['invoice_id'])->profitLoss;

    // Each invoice should only has one profit & loss
    $pnl ? $this->halt() : null;

    return $data;
  }
}
