<?php

if (!function_exists('get_code')) {
  /**
   * Generates a unique code for a model.
   *
   * @param  Illuminate\Database\Eloquent\Model  $model The Eloquent model for which the code is generated
   * @param  string|null  $prefix The prefix to be added to the generated code
   * @param  string  $column Generate a unique code based on this column
   * @param  int  $numberDigit The number of digits for the generated code
   * @param  bool  $reset_on_prefix_change Flag indicating whether to reset the code when the prefix changes
   * @return string The generated unique code
   */
  function get_code(
    Illuminate\Database\Eloquent\Model $model,
    string|null $prefix = null,
    string $column = 'code',
    int $numberDigit = 5,
    bool $reset_on_prefix_change = true
  ): string {
    $table = $model->getTable();

    // Create prefix based on table names if not provided
    blank($prefix) && $prefix = substr($table, 0, 3) . '-';

    // Remove spaces and other unwanted characters
    $prefix = str_replace(' ', '', trim($prefix));

    // Capitalize all characters
    $prefix = mb_strtoupper($prefix);

    // Calculate the total length of the generated code
    $length = strlen($prefix) + $numberDigit;

    // Generate the unique code using IdGenerator
    $code = \Haruncpi\LaravelIdGenerator\IdGenerator::generate([
      'table' => $table,
      'field' => $column,
      'length' => $length,
      'prefix' => $prefix,
      'reset_on_prefix_change' => $reset_on_prefix_change,
    ]);

    return $code;
  }
}