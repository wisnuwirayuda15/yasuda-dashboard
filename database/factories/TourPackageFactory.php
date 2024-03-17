<?php

namespace Database\Factories;

use App\Models\Regency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TourPackage;

class TourPackageFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = TourPackage::class;

  /**
   * Define the model's default state.
   */
  public function definition(): array
  {
    $city = Regency::select('name')->inRandomOrder()->first();
    $duration = $this->faker->randomDigitNotNull();
    $name = "$duration D - {$city->name}";

    return [
      'image' => "https://picsum.photos/seed/{$this->faker->unique()->word()}/2048",
      'name' => $name,
      'city' => $city->name,
      'description' => $this->faker->text(),
      'duration' => $this->faker->randomDigitNotNull(),
      'order_total' => $this->faker->numberBetween(0, 1000),
      'price' => $this->faker->numberBetween(1000000, 5000000),
    ];
  }
}
