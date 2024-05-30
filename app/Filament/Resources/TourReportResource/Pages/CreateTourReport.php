<?php

namespace App\Filament\Resources\TourReportResource\Pages;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\TourReportResource;

class CreateTourReport extends CreateRecord
{
  protected static string $resource = TourReportResource::class;

  protected static bool $canCreateAnother = false;

  public function beforeFill(): void
  {
    $inv = Invoice::where('code', request('invoice'))->firstOrFail();

    $pnl = $inv->profitLoss;

    $tr = $inv->tourReport;

    // All order fleets must have tour leader before creating tour report
    $tourLeaderNotAllSet = $inv->order->whereHas('orderFleets', function (Builder $query) {
      $query->whereNull('tour_leader_id');
    })->exists();

    // Invoice must have profit & loss before creating tour report
    if (!$pnl || $tourLeaderNotAllSet) {
      redirect(InvoiceResource::getUrl('view', ['record' => $inv->id]));
    }

    // Each invoice should only has one tour report
    if ($tr) {
      redirect(TourReportResource::getUrl('view', ['record' => $tr->id]));
    }
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $inv = Invoice::findOrFail($data['invoice_id']);

    $pnl = $inv->profitLoss;

    $tr = $inv->tourReport;

    $tourLeaderNotAllSet = $inv->order->whereHas('orderFleets', function (Builder $query) {
      $query->whereNull('tour_leader_id');
    })->exists();

    // Invoice must have profit & loss before creating tour report
    // Each invoice should only has one tour report
    // All order fleets must have tour leader before creating tour report
    if (!$pnl || $tr || $tourLeaderNotAllSet) {
      $this->halt();
    }

    return $data;
  }
}
