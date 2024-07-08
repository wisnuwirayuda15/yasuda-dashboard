<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use Filament\Actions;
use App\Enums\CashFlow;
use App\Enums\FleetCategory;
use App\Models\LoyaltyPoint;
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
      Actions\Action::make('view_invoice')
        ->label('Lihat Invoice')
        ->icon(InvoiceResource::getNavigationIcon())
        ->color('info')
        ->url(InvoiceResource::getUrl('view', ['record' => $pnl->invoice->id])),
      Actions\Action::make('create_and_update_loyalty_point')
        ->icon(LoyaltyPointResource::getNavigationIcon())
        ->label(($loyaltyPoint ? 'Perbarui' : 'Buat') . ' Loyalty Point')
        ->color($loyaltyPoint ? 'warning' : 'success')
        ->modal(false)
        ->action(function (Actions\Action $action) use ($loyaltyPoint, $approved, $pnl) {
          if (!$approved) {
            Notification::make()
              ->danger()
              ->title('Failed')
              ->body("Profit & Loss not approved!")
              ->send();

            $action->cancel();
          }

          $inv = $pnl->invoice;

          $order = $inv->order;

          [$mediumTotal, $bigTotal, $legrestTotal] = [0, 0, 0];

          foreach ($order->orderFleets as $orderFleet) {
            $fleet = $orderFleet->fleet;
            match ($fleet->category->value) {
              FleetCategory::MEDIUM->value => $mediumTotal++,
              FleetCategory::BIG->value => $bigTotal++,
              FleetCategory::LEGREST->value => $legrestTotal++,
            };
          }

          $bonus = $mediumTotal * $pnl->medium_subs_bonus + $bigTotal * $pnl->big_subs_bonus + $legrestTotal * $pnl->legrest_subs_bonus;

          if ($loyaltyPoint) {
            $loyaltyPoint->update([
              'amount' => $bonus,
            ]);

            Notification::make()
              ->success()
              ->title('Success')
              ->body("Loyalty Point for <strong>{$inv->code}</strong> updated")
              ->send();
          } else {
            $inv->loyaltyPoint()->create([
              'cash_status' => CashFlow::IN->value,
              'description' => '<p>Tambahan saldo bonus langganan</p>',
              'amount' => $bonus,
            ]);

            Notification::make()
              ->success()
              ->title('Success')
              ->body("Loyalty Point for <strong>{$inv->code}</strong> created")
              ->send();
          }

          redirect(LoyaltyPointResource::getUrl('index'));
        }),
      Actions\EditAction::make(),
    ];
  }
}
