<?php

namespace App\Filament\Exports;

use App\Enums\FleetPaymentStatus;
use Carbon\Carbon;
use App\Models\OrderFleet;
use App\Enums\FleetCategory;
use App\Enums\FleetSeat;
use App\Enums\OrderFleetStatus;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class OrderFleetExporter extends Exporter
{
  protected static ?string $model = OrderFleet::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('id')
        ->label('ID'),
      ExportColumn::make('code'),
      ExportColumn::make('order.customer.name'),
      ExportColumn::make('tourLeader.name'),
      ExportColumn::make('trip_date')
        ->formatStateUsing(fn($state): string => $state->translatedFormat('d/m/Y')),
      ExportColumn::make('remaining_day')
        ->label('Remaining Day')
        ->state(
          function (OrderFleet $record): string {
            $date = $record->trip_date;
            return match (true) {
              $date->isToday() => OrderFleetStatus::ON_TRIP->getLabel(),
              $date->isPast() => OrderFleetStatus::FINISHED->getLabel(),
              default => today()->diffInDays($date),
            };
          }
        ),
      ExportColumn::make('status')
        ->state(
          function (OrderFleet $record): string {
            $date = $record->trip_date;
            $order = $record->order()->exists();
            return match (true) {
              $order => OrderFleetStatus::BOOKED->getLabel(),
              $date->isToday() => OrderFleetStatus::ON_TRIP->getLabel(),
              $date->isPast() => OrderFleetStatus::FINISHED->getLabel(),
              default => OrderFleetStatus::READY->getLabel(),
            };
          }
        ),
      ExportColumn::make('fleet.name')
        ->label('Mitra Armada'),
      ExportColumn::make('fleet.category')
        ->label('Jenis')
        ->formatStateUsing(fn(FleetCategory $state) => $state->getLabel()),
      ExportColumn::make('fleet.seat_set')
        ->label('Seat Set')
        ->formatStateUsing(fn(FleetSeat $state) => $state->getLabel()),
      ExportColumn::make('trip_day')
        ->label('Hari')
        ->state(fn(OrderFleet $record): string => $record->trip_date->translatedFormat('l')),
      ExportColumn::make('trip_month')
        ->label('Bulan')
        ->state(fn(OrderFleet $record): string => $record->trip_date->translatedFormat('F')),
      ExportColumn::make('payment_status')
        ->label('Status Pembayaran')
        ->formatStateUsing(fn(FleetPaymentStatus $state) => $state->getLabel()),
      ExportColumn::make('payment_date')
        ->label('Tgl. Bayar')
        ->formatStateUsing(fn(?Carbon $state): ?string => $state ? $state->translatedFormat('d/m/Y') : null),
      ExportColumn::make('payment_amount')
        ->label('Jumlah Bayar'),
      ExportColumn::make('created_at'),
      ExportColumn::make('updated_at'),
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your order fleet export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
