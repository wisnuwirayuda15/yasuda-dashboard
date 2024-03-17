<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;

class CustomerFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Customer::class;

  /**
   * Define the model's default state.
   */
  public function definition(): array
  {
    return [
      'name' => $this->faker->name(),
      'institution' => $this->faker->word(),
      'category' => $this->faker->randomElement(['umum', 'sd', 'tk', 'smp', 'sma']),
      'email' => $this->faker->safeEmail(),
      'phone' => $this->faker->phoneNumber(),
    ];
  }
}
