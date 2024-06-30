<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use App\Enums\CustomerStatus;
use App\Enums\CustomerCategory;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
  protected static ?string $model = Customer::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('id')
        ->label('ID'),
      ExportColumn::make('code'),
      ExportColumn::make('name'),
      ExportColumn::make('category')
        ->formatStateUsing(fn(CustomerCategory $state) => $state->getLabel()),
      ExportColumn::make('status')
        ->formatStateUsing(fn(CustomerStatus $state) => $state->getLabel()),
      ExportColumn::make('address'),
      ExportColumn::make('regency.name')
        ->label('Kota'),
      ExportColumn::make('district.name')
        ->label('Kabupaten'),
      ExportColumn::make('headmaster'),
      ExportColumn::make('operator'),
      ExportColumn::make('phone'),
      ExportColumn::make('email'),
      ExportColumn::make('balance')
        ->label('Saldo')
        ->state(fn(Customer $record) => $record->getBalance()),
      ExportColumn::make('lat')
        ->label('Latitude'),
      ExportColumn::make('lng')
        ->label('Longitude'),
      ExportColumn::make('created_at'),
      ExportColumn::make('updated_at'),
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your customer export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
