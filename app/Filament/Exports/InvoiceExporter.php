<?php

namespace App\Filament\Exports;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Enums\InvoiceStatus;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class InvoiceExporter extends Exporter
{
  protected static ?string $model = Invoice::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('id')
        ->label('ID'),
      ExportColumn::make('code'),
      ExportColumn::make('order.customer.name'),
      ExportColumn::make('order.trip_date')
        ->label('Tanggal')
        ->formatStateUsing(fn(Carbon $state): string => $state->translatedFormat('d/m/Y')),
      ExportColumn::make('total_transactions')
        ->label('Total Tagihan')
        ->state(fn(Invoice $record): float|int => $record->getTotalTransactions()),
      ExportColumn::make('status')
        ->state(fn(Invoice $record): string|null => $record->getPaymentStatus()->getLabel()),
      ExportColumn::make('created_at'),
      ExportColumn::make('updated_at'),
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your invoice export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
