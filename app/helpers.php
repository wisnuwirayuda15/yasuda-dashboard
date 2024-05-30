<?php

use Illuminate\Support\Number;
use Haruncpi\LaravelIdGenerator\IdGenerator;

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
    if (blank($number)) $number = 0;
    
    $idrString = Number::currency($number, 'IDR', $asRupiah ? 'id' : null);

    return str_replace(',00', '', $idrString);
  }
}

if (!function_exists('getUrlQueryParameters')) {
  /**
   * Retrieves the query parameters from a given URL.
   *
   * @param string $url The URL from which to extract the query parameters.
   * @return array An associative array containing the query parameters.
   */
  function getUrlQueryParameters($url)
  {
    $parsedUrl = parse_url($url);

    $queryString = $parsedUrl['query'] ?? '';

    parse_str($queryString, $parameters);
    
    return $parameters;
  }
}