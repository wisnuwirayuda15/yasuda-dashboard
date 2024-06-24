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
    return [
      Actions\Action::make('view_invoice')
        ->label('Lihat Invoice')
        ->icon(InvoiceResource::getNavigationIcon())
        ->color('info')
        ->url(InvoiceResource::getUrl('view', ['record' => $this->getRecord()->invoice->id])),
      Actions\Action::make('create_loyalty_point')
        ->label('Buat Loyalty Point')
        ->icon(LoyaltyPointResource::getNavigationIcon())
        ->color(Color::Yellow)
        ->disabled(fn() => (bool) $this->getRecord()->invoice->loyaltyPoint)
        ->action(function () {
          $pnl = $this->getRecord();

          $inv = $pnl->invoice;

          if ((bool) !$inv->loyaltyPoint) {
            $order = $pnl->invoice->order;

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

            $loyaltyPoint = $inv->loyaltyPoint()->create([
              'cash_status' => CashFlow::IN->value,
              'description' => '<p>Tambahan saldo bonus langganan</p>',
              'amount' => $bonus,
            ]);

            Notification::make()
              ->success()
              ->title('Success')
              ->body("Loyalty Point for <strong>{$inv->code}</strong> created")
              ->send();

            redirect(LoyaltyPointResource::getUrl('view', ['record' => $loyaltyPoint->id]));
          } else {
            Notification::make()
              ->danger()
              ->title('Failed')
              ->body("Loyalty Point for <strong>{$inv->code}</strong> already exist")
              ->send();
          }
        }),
      Actions\EditAction::make(),
    ];
  }
}
