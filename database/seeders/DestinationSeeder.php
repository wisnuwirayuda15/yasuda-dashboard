<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Enums\DestinationType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DestinationSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $types = array_map(fn($x) => $x->value, DestinationType::cases());

    for ($x = 1; $x <= 15; $x++) {
      Destination::create([
        'name' => ucwords(fake('en_US')->unique()->streetName()),
        'type' => fake()->randomElement($types),
        'marketing_name' => fake()->name(),
        'marketing_phone' => fake()->phoneNumber(),
        'weekday_price' => fake()->numberBetween(100000, 1000000),
        'weekend_price' => fake()->numberBetween(100000, 1000000),
        'high_season_price' => fake()->numberBetween(100000, 1000000),
      ]);
    }
  }
}
