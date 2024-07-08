<?php

namespace App\Filament\Exports;

use Carbon\Carbon;
use App\Models\ProfitLoss;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class ProfitLossExporter extends Exporter
{
  protected static ?string $model = ProfitLoss::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('id')
        ->label('ID'),
      ExportColumn::make('invoice.code'),
      ExportColumn::make('invoice.order.code'),
      ExportColumn::make('invoice.order.customer.name')
        ->label('Customer Name'),
      ExportColumn::make('net_sales')
        ->state(function (ProfitLoss $record): ?float {
          $inv = $record->invoice;
          $mainCosts = $inv->main_costs;
          $totalPrices = array_sum(array_map(fn($cost) => $cost['qty'] * $cost['price'], $mainCosts)) ?: 0;
          $totalCashbacks = array_sum(array_map(fn($cost) => $cost['qty'] * $cost['cashback'], $mainCosts)) ?: 0;
          $totalNetTransactions = $totalPrices - $totalCashbacks;
          return $totalNetTransactions;
        }),
      ExportColumn::make('adjusted_income')
        ->label('Income (Plan)'),
      ExportColumn::make('actual_income')
        ->label('Income (Actual)')
        ->state(function (ProfitLoss $record): float|string {
          return $record->invoice->tourReport
            ? $record->adjusted_income + $record->invoice->tourReport->difference
            : '-';
        }),
      ExportColumn::make('invoice.order.trip_date')
        ->label('Tanggal')
        ->formatStateUsing(fn(Carbon $state): string => $state->translatedFormat('d/m/Y')),
      ExportColumn::make('created_at'),
      ExportColumn::make('updated_at'),
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your profit loss export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
