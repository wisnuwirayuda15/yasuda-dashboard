<?php

namespace App\Filament\Resources\TourReportResource\Pages;

use App\Models\Invoice;
use App\Models\TourReport;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\TourReportResource;
use EightyNine\Approvals\Models\ApprovableModel;

class CreateTourReport extends CreateRecord
{
  protected static string $resource = TourReportResource::class;

  protected static bool $canCreateAnother = false;

  public function beforeFill(): void
  {
    $inv = Invoice::where('code', request('invoice'))->firstOrFail();

    $pnlApproved = (bool) $inv->profitLoss?->isApprovalCompleted();

    if (!$pnlApproved) {
      Notification::make()
        ->danger()
        ->title('Ooppsss...')
        ->body('Profit & Loss not yet approved!')
        ->send();

      redirect(InvoiceResource::getUrl('view', ['record' => $inv->id]));
    }

    $pnl = $inv->profitLoss;

    $tr = TourReport::withoutGlobalScopes()->where('invoice_id', $inv->id)->first();

    // All order fleets must have tour leader before creating tour report
    $tourLeaderNotAllSet = $inv->order->whereHas('orderFleets', function (Builder $query) {
      $query->whereNull('employee_id');
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

    $tourLeaderNotAllSet = $inv->order->whereHas('orderFleets', function (Builder $query) {
      $query->whereNull('employee_id');
    })->exists();

    // All order fleets must have tour leader before creating tour report
    if ($tourLeaderNotAllSet) {
      $this->halt();
    }

    return $data;
  }

  protected function handleRecordCreation(array $data): Model
  {
    $model = static::getModel()::create($data);

    instant_approval($data, $model);

    return $model;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
