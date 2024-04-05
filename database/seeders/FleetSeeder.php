<?php

namespace Database\Seeders;

use App\Enums\FleetCategory;
use App\Enums\FleetSeat;
use App\Models\Fleet;
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

    $seats = array_map(fn($x) => $x->value, FleetSeat::cases());

    $categories = array_map(fn($x) => $x->value, FleetCategory::cases());

    foreach ($busses as $bus) {
      $code = get_code(new Fleet, 'BUS-');

      $image = "https://picsum.photos/seed/$code/1280/720";

      Fleet::create([
        'code' => $code,
        'image' => $image,
        'name' => strtoupper($bus),
        'description' => fake()->text(),
        'seat_set' => fake()->randomElement($seats),
        'category' => fake()->randomElement($categories),
        'pic_name' => fake()->name(),
        'pic_phone' => fake()->phoneNumber(),
      ]);
    }
  }
}
