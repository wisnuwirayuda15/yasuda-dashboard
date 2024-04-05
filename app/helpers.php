<?php
use Haruncpi\LaravelIdGenerator\IdGenerator;

if (!function_exists('get_code')) {
  /**
   * Generates a unique code for a model.
   *
   * @param  Illuminate\Database\Eloquent\Model  $model
   * @param  string  $prefix
   * @param  string  $column
   * @param  int  $numberDigit
   * @return string
   */
  function get_code(
    Illuminate\Database\Eloquent\Model $model,
    string|null $prefix = null,
    string $column = 'code',
    int $numberDigit = 5,
    bool $reset_on_prefix_change = true
  ): string {
    $table = $model->getTable();

    // create prefix based on table names if not provided
    is_null($prefix) && $prefix = substr($table, 0, 3) . '-';

    // remove spaces and other unwanted characters
    $prefix = str_replace(' ', '', trim($prefix));

    // capitalize all characters
    $prefix = strtoupper($prefix);

    // n digit(s) of prefix, 5 digits of number. Example: PRF-00001
    $length = strlen($prefix) + $numberDigit;

    $code = IdGenerator::generate([
      'table' => $table,
      'field' => $column,
      'length' => $length,
      'prefix' => $prefix,
      'reset_on_prefix_change' => $reset_on_prefix_change,
    ]);

    return $code;
  }
}