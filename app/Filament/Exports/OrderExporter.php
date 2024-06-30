<?php

namespace App\Filament\Exports;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Destination;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
  protected static ?string $model = Order::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('id')
        ->label('ID'),
      ExportColumn::make('code'),
      ExportColumn::make('customer.name'),
      ExportColumn::make('trip_date')
        ->label('Tanggal')
        ->formatStateUsing(fn(Carbon $state): string => $state->translatedFormat('d/m/Y')),
      ExportColumn::make('regency.name')
        ->label('Kota'),
      ExportColumn::make('destinations')
        ->label('Destinasi')
        ->formatStateUsing(fn(string $state): string => Destination::find($state)?->name),
      ExportColumn::make('created_at'),
      ExportColumn::make('updated_at'),
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your order export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
