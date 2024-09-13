<?php

use App\Models\Regency;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\District;
use App\Models\Province;
use Illuminate\Support\Number;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use EightyNine\Approvals\Models\ApprovableModel;

if (!function_exists('emp_code')) {
  /**
   * Generates a unique code based on the provided model, prefix, connector, column name, number of digits, and reset flag.
   *
   * @param Illuminate\Database\Eloquent\Model $model The model to generate the code for.
   * @param string|null $prefix The prefix for the generated code. If not provided, it will be derived from the model's table name.
   * @param string|bool $connector The connector to use between the prefix and the generated code. If not provided, a hyphen will be used as the connector.
   * @param string $column The unique code is obtained based on this column of the model. Default is 'code'.
   * @param int $numberDigit The number of digits to use for the generated code. Default is 5.
   * @param bool $resetOnPrefixChange Whether to reset the generated code when the prefix changes. Default is true.
   * @return string The generated unique code.
   */
  function emp_code(
    Illuminate\Database\Eloquent\Model $model,
    string|null $prefix,
    string $column = 'code',
    int $numberDigit = 4,
    bool $resetOnPrefixChange = true
  ): string {
    $table = $model->getTable();

    // Create prefix based on table names if not provided
    blank($prefix) && $prefix = substr($table, 0, 3);

    // Remove spaces and other unwanted characters
    $prefix = str_replace(' ', '', trim($prefix));

    // Capitalize all characters and add a connector
    $prefix = mb_strtoupper($prefix);

    // Calculate the total length of the generated code
    $length = strlen($prefix) + $numberDigit;

    // Generate the unique code using IdGenerator
    return IdGenerator::generate([
      'table' => $table,
      'field' => $column,
      'length' => $length,
      'prefix' => $prefix,
      'reset_on_prefix_change' => $resetOnPrefixChange,
    ]);
  }
}

if (!function_exists('get_code')) {
  /**
   * Generates a unique code based on the provided model, prefix, connector, column name, number of digits, and reset flag.
   *
   * @param Illuminate\Database\Eloquent\Model $model The model to generate the code for.
   * @param string|null $prefix The prefix for the generated code. If not provided, it will be derived from the model's table name.
   * @param string|bool $connector The connector to use between the prefix and the generated code. If not provided, a hyphen will be used as the connector.
   * @param string $column The unique code is obtained based on this column of the model. Default is 'code'.
   * @param int $numberDigit The number of digits to use for the generated code. Default is 5.
   * @param bool $resetOnPrefixChange Whether to reset the generated code when the prefix changes. Default is true.
   * @return string The generated unique code.
   */
  function get_code(
    Illuminate\Database\Eloquent\Model $model,
    string|null $prefix = null,
    string|bool $connector = '-',
    string $column = 'code',
    int $numberDigit = 5,
    bool $resetOnPrefixChange = true
  ): string {
    $table = $model->getTable();

    // Create prefix based on table names if not provided
    blank($prefix) && $prefix = substr($table, 0, 3);

    // Remove spaces and other unwanted characters
    $prefix = str_replace(' ', '', trim($prefix));

    // Capitalize all characters and add a connector
    $prefix = mb_strtoupper($prefix) . $connector ?? null;

    // Calculate the total length of the generated code
    $length = strlen($prefix) + $numberDigit;

    // Generate the unique code using IdGenerator
    return IdGenerator::generate([
      'table' => $table,
      'field' => $column,
      'length' => $length,
      'prefix' => $prefix,
      'reset_on_prefix_change' => $resetOnPrefixChange,
    ]);
  }
}

if (!function_exists('get_random_code')) {
  /**
   * Generate a unique random number code for a model.
   *
   * @param Illuminate\Database\Eloquent\Model $model The model to generate the code for.
   * @param string|null $prefix The prefix for the generated code. If not provided, it will be derived from the model's table name.
   * @param string|bool $connector The connector to use between the prefix and the generated code. If not provided, a hyphen will be used as the connector.
   * @param string $column The unique code is obtained based on this column of the model. Default is 'code'.
   * @param int $numberDigit The number of digits to use for the generated code. Default is 5.
   * @return string The generated unique code.
   */
  function get_random_code(
    Illuminate\Database\Eloquent\Model $model,
    string|null $prefix = null,
    string|bool $connector = '-',
    string $column = 'code',
    int $numberDigit = 6,
  ): string {
    // If prefix is not provided, use the first 3 letters of the table name in uppercase
    if (blank($prefix)) {
      $prefix = strtoupper(substr($model->getTable(), 0, 3));
    } else {
      $prefix = strtoupper(trim($prefix));
    }

    // Trim the connector if it is a string
    if (is_string($connector)) {
      $connector = trim($connector);
    }

    // Trim the column name
    $column = trim($column);

    do {
      // Generate a random number with the specified number of digits
      $randomNumber = fake()->numerify(str_repeat('#', $numberDigit));

      // Construct the code with prefix and connector if provided
      $code = $prefix . ($connector !== false ? $connector : '') . $randomNumber;

      // Check if the code exists in the model's table
      $exists = $model->newQuery()->where($column, $code)->exists();
    } while ($exists);

    return $code;
  }
}

if (!function_exists('idr')) {
  /**
   * A function that formats the given value as an Indonesian Rupiah currency.
   *
   * @param int|float $number The number value to be formatted as IDR currency.
   * @param bool $asRupiah Whether to display the currency as Rupiah.
   * @return string The formatted IDR currency value.
   */
  function idr(int|float|null $number, bool $asRupiah = true): string
  {
    if (blank($number)) {
      return 'Rp -';
    }

    $currencyString = Number::currency($number, 'IDR', $asRupiah ? 'id' : null);

    return str_replace(',00', '', $currencyString);
  }
}

if (!function_exists('getUrlQueryParameters')) {
  /**
   * Retrieves the query parameters from a given URL.
   *
   * @param string $url The URL from which to extract the query parameters.
   * @return array An associative array containing the query parameters.
   */
  function getUrlQueryParameters($url): array
  {
    $parsedUrl = parse_url($url);

    $queryString = $parsedUrl['query'] ?? '';

    parse_str($queryString, $parameters);

    return $parameters;
  }
}

if (!function_exists('enum_map')) {
  /**
   * Maps the values of an array using a callback function.
   *
   * @param array $enum The array to map over.
   * @return array The array of mapped values.
   */
  function enum_map(array $enum): array
  {
    return array_map(fn($x) => $x->value, $enum);
  }
}

if (!function_exists('RegionSelects')) {
  function RegionSelects(bool $distritcs = true): Component
  {
    return Group::make([
      Select::make('province_id')
        ->required()
        ->label('Provinsi')
        ->options(Province::pluck('name', 'id'))
        ->placeholder('Pilih provinsi')
        ->formatStateUsing(function (Get $get) {
          $regency = Regency::find($get('regency_id'));
          return $regency ? $regency->province_id : null;
        })
        ->afterStateUpdated(function (Set $set) {
          $set('regency_id', null);
          $set('district_id', null);
        })
        ->loadingIndicator(),
      Select::make('regency_id')
        ->required()
        ->label('Kabupaten / Kota')
        ->disabled(fn(Get $get) => !$get('province_id'))
        ->placeholder('Pilih kabupaten / kota')
        ->options(fn(Get $get) => Regency::where('province_id', $get('province_id'))->pluck('name', 'id'))
        ->afterStateUpdated(fn(Set $set) => $set('district_id', null))
        ->loadingIndicator(),
      Select::make('district_id')
        ->live()
        ->required()
        ->visible($distritcs)
        ->label('Kecamatan')
        ->disabled(fn(Get $get) => !$get('regency_id'))
        ->placeholder('Pilih kecamatan')
        ->options(fn(Get $get) => District::where('regency_id', $get('regency_id'))->pluck('name', 'id')),
    ])
      ->columnSpanFull()
      ->columns($distritcs ? 3 : 2);
  }
}

if (!function_exists('instant_approval')) {
  /**
   * A function for instant approval based on certain conditions.
   *
   * @param array $data The data array to be processed.
   * @param string|Model|ApprovableModel $model The model instance to be approved.
   * @param string $fieldName The name of the field (default is 'submission').
   * @return void
   */
  function instant_approval(
    string|ApprovableModel $model,
    array $data = [],
    string $fieldName = 'submission'
  ): void {
    if (env('INSTANT_APPROVAL', true)) {
      if ($model instanceof ApprovableModel) {
        if (!$model->isApprovalCompleted()) {
          $user = auth()->user();

          $superAdmin = $user->isSuperAdmin();

          $shouldSubmit = $superAdmin || (isset($data[$fieldName]) && $data[$fieldName] === true);

          if ($shouldSubmit) {
            if (!$model->isSubmitted()) {
              $model->submit(user: $user);
            }

            if ($superAdmin) {
              $model::withoutGlobalScopes()->find($model->id)->approve(user: $user);
            }
          }
        }
      } else {
        Notification::make()
          ->warning()
          ->title('Approval failed')
          ->body('The model is not an instance of <strong>ApprovableModel</strong>')
          ->persistent()
          ->send();
      }
    }
  }
}