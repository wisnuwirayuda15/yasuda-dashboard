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
    $types = enum_map(DestinationType::cases());

    for ($x = 1; $x <= 100; $x++) {
      Destination::create([
        'name' => ucwords(fake('en_US')->unique()->streetName()),
        'type' => fake()->randomElement($types),
        'marketing_name' => fake()->name(),
        'marketing_phone' => fake()->numerify('+6281#########'),
        'weekday_price' => (int) round(fake()->numberBetween(15000, 30000), -3),
        'weekend_price' => (int) round(fake()->numberBetween(31000, 40000), -3),
        'high_season_price' => (int) round(fake()->numberBetween(41000, 50000), -3),
      ]);
    }
  }
}
