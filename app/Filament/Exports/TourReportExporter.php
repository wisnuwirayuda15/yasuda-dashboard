<?php

namespace App\Filament\Exports;

use App\Models\TourReport;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TourReportExporter extends Exporter
{
  protected static ?string $model = TourReport::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('id')
        ->label('ID'),
      ExportColumn::make('invoice.code'),
      ExportColumn::make('invoice.order.code'),
      ExportColumn::make('invoice.order.customer.name'),
      ExportColumn::make('customer_repayment')
        ->label('Pembayaran Customer'),
      ExportColumn::make('difference')
        ->label('Selisih'),
      ExportColumn::make('created_at'),
      ExportColumn::make('updated_at'),
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your tour report export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
