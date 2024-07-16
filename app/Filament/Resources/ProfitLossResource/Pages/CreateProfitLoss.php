<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use App\Models\Invoice;
use App\Models\ProfitLoss;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\ProfitLossResource;
use EightyNine\Approvals\Models\ApprovableModel;

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

  protected function handleRecordCreation(array $data): Model
  {
    $model = static::getModel()::create($data);

    // instant_approval($data, $model);

    return $model;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
