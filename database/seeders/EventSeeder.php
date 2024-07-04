<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EventSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    for ($x = 1; $x <= 50; $x++) {
      Event::create([
        'title' => ucwords(fake()->unique()->words(fake()->numberBetween(1, 3), true)),
        'date' => fake()->dateTimeBetween('now', '+3 month'),
        'description' => '<p>' . fake()->text() . '</p>',
      ]);
    }
  }
}
