<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Bus;

class BusFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Bus::class;

  /**
   * Define the model's default state.
   */
  public function definition(): array
  {
    return [
      'image' => "https://picsum.photos/seed/{$this->faker->unique()->word()}/1366/768",
      'name' => 'Bus ' . ucwords($this->faker->streetName()),
      'description' => $this->faker->text(),
      'seat_total' => $this->faker->numberBetween(25, 50),
      'right_seat' => $this->faker->numberBetween(2, 3),
      'left_seat' => $this->faker->numberBetween(2, 3),
      'type' => $this->faker->randomElement(['big', 'medium']),
      'price' => $this->faker->numberBetween(100000, 1000000),
    ];
  }
}
