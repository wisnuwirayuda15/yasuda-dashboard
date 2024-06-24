<?php

namespace Database\Seeders;

use App\Models\Fleet;
use App\Enums\BigFleetSeat;
use App\Enums\FleetCategory;
use App\Enums\MediumFleetSeat;
use App\Enums\LegrestFleetSeat;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FleetSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $busses = [
      'Mayasari',
      'Hiba',
      'Sinar Jaya',
      'Haryanto',
      'Rosalia Indah',
      'Lorena',
      'Dewi Sri',
      'Budiman',
      'SAN',
      'Nusantara',
      'Pangeran',
      'Tidal',
    ];

    $mediumSeats = enum_map(MediumFleetSeat::cases());

    $bigSeats = enum_map(BigFleetSeat::cases());

    $legrestSeats = enum_map(LegrestFleetSeat::cases());

    $categories = enum_map(FleetCategory::cases());

    foreach ($busses as $bus) {
      $code = fake()->numerify('########');

      $image = "https://picsum.photos/seed/$code/1280/720";

      $category = fake()->randomElement($categories);

      $seat = match ($category) {
        FleetCategory::MEDIUM->value => fake()->randomElement($mediumSeats),
        FleetCategory::BIG->value => fake()->randomElement($bigSeats),
        FleetCategory::LEGREST->value => fake()->randomElement($legrestSeats),
        default => fake()->randomElement($mediumSeats),
      };

      Fleet::create([
        'image' => $image,
        'name' => strtoupper($bus),
        'description' => fake()->text(),
        'category' => $category,
        'seat_set' => $seat,
        'pic_name' => fake()->name(),
        'pic_phone' => fake()->numerify('+6281#########'),
      ]);
    }
  }
}
