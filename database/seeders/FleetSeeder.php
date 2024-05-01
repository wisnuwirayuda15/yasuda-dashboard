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

    $mediumSeats = array_map(fn($x) => $x->value, MediumFleetSeat::cases());
    $bigSeats = array_map(fn($x) => $x->value, BigFleetSeat::cases());
    $legrestSeats = array_map(fn($x) => $x->value, LegrestFleetSeat::cases());

    $categories = array_map(fn($x) => $x->value, FleetCategory::cases());

    foreach ($busses as $bus) {
      $code = substr(md5(rand()), 0, 10);

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
        'pic_phone' => fake()->phoneNumber(),
      ]);
    }
  }
}
