<?php

namespace App\Filament\Imports;

use App\Models\Customer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CustomerImporter extends Importer
{
  protected static ?string $model = Customer::class;

  public static function getColumns(): array
  {
    return [
      ImportColumn::make('code')
        ->requiredMapping()
        ->rules(['required', 'max:255']),
      ImportColumn::make('name')
        ->requiredMapping()
        ->rules(['required', 'max:255']),
      ImportColumn::make('address')
        ->requiredMapping()
        ->rules(['required', 'max:255']),
      ImportColumn::make('category')
        ->requiredMapping()
        ->rules(['required', 'max:50']),
      ImportColumn::make('regency')
        ->requiredMapping()
        ->relationship()
        ->rules(['required']),
      ImportColumn::make('district')
        ->requiredMapping()
        ->relationship()
        ->rules(['required']),
      ImportColumn::make('headmaster')
        ->requiredMapping()
        ->rules(['required', 'max:255']),
      ImportColumn::make('operator')
        ->requiredMapping()
        ->rules(['required', 'max:255']),
      ImportColumn::make('phone')
        ->requiredMapping()
        ->rules(['required', 'max:255']),
      ImportColumn::make('email')
        ->rules(['max:255']),
      ImportColumn::make('lat')
        ->rules(['numeric', 'max:255']),
      ImportColumn::make('lng')
        ->rules(['numeric', 'max:255']),
      ImportColumn::make('status')
        ->requiredMapping()
        ->rules(['required', 'max:50']),
    ];
  }

  public function resolveRecord(): ?Customer
  {
    // return Customer::firstOrNew([
    //     // Update existing records, matching them by `$this->data['column_name']`
    //     'email' => $this->data['email'],
    // ]);

    return new Customer();
  }

  public static function getCompletedNotificationBody(Import $import): string
  {
    $body = 'Your customer import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

    if ($failedRowsCount = $import->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
    }

    return $body;
  }
}
