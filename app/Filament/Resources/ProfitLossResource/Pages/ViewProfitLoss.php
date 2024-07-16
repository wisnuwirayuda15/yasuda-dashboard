<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use Filament\Actions;
use App\Enums\CashFlow;
use App\Models\OrderFleet;
use App\Models\ProfitLoss;
use App\Models\TourReport;
use App\Enums\FleetCategory;
use App\Models\LoyaltyPoint;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\ProfitLossResource;
use App\Filament\Resources\LoyaltyPointResource;

class ViewProfitLoss extends ViewRecord
{
  protected static string $resource = ProfitLossResource::class;

  protected function getHeaderActions(): array
  {
    $pnl = $this->getRecord();

    $loyaltyPoint = $pnl->invoice->loyaltyPoint;

    $approved = $pnl->isApprovalCompleted();

    return [
      ActionGroup::make([
        DeleteAction::make()
          ->action(function (ProfitLoss $record, DeleteAction $action) {
            $tourReport = TourReport::withoutGlobalScopes()->where('invoice_id', $record->invoice_id)->exists();
            if ($tourReport) {
              Notification::make()
                ->danger()
                ->title('Delete failed')
                ->body("Invoice <strong>{$record->invoice->code}</strong> has Tour Report")
                ->send();
              $action->cancel();
            }
            $record->delete();
          }),
        Action::make('view_invoice')
          ->label('Lihat Invoice')
          ->icon(InvoiceResource::getNavigationIcon())
          ->color('info')
          ->url(InvoiceResource::getUrl('view', ['record' => $pnl->invoice->id])),
        Action::make('create_or_update_loyalty_point')
          ->icon(LoyaltyPointResource::getNavigationIcon())
          ->label(($loyaltyPoint ? 'Perbarui' : 'Buat') . ' Loyalty Point')
          ->color($loyaltyPoint ? 'warning' : 'success')
          ->modal(false)
          ->visible(fn() => $approved)
          ->action(function (Action $action) use ($pnl) {
            $lp = $pnl->createOrUpdateLoyaltyPoint();
            
            if (!$lp) {
              Notification::make()
                ->danger()
                ->title('Failed')
                ->body("Profit & Loss belum disetujui!")
                ->send();

              $action->cancel();
            }

            redirect(LoyaltyPointResource::getUrl('index'));
          }),
      ]),
      EditAction::make(),
      Action::make('not_approved')
        ->disabled()
        ->label('Not Aprroved')
        ->color('danger')
        ->icon('heroicon-s-x-circle')
        ->hidden($approved),
    ];
  }
}
