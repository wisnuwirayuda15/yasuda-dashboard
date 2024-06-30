<?php

namespace App\Filament\Exports;

use App\Enums\FleetCategory;
use App\Enums\FleetSeat;
use App\Models\Fleet;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class FleetExporter extends Exporter
{
  protected static ?string $model = Fleet::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('id')
        ->label('ID'),
      ExportColumn::make('name'),
      ExportColumn::make('description'),
      ExportColumn::make('category')
        ->formatStateUsing(fn(FleetCategory $state) => $state->getLabel()),
      ExportColumn::make('seat_set')
        ->formatStateUsing(fn(FleetSeat $state) => $state->getLabel()),
      ExportColumn::make('pic_name')
        ->label('PIC'),
      ExportColumn::make('pic_phone')
        ->label('Phone'),
      ExportColumn::make('created_at'),
      ExportColumn::make('updated_at'),
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your fleet export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
