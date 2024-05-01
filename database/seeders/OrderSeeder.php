<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Regency;
use App\Models\Customer;
use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    for ($i = 0; $i < 20; $i++) {
      Order::create([
        'code' => get_code(new Order, 'OR'),
        'customer_id' => Customer::inRandomOrder()->value('id'),
        'regency_id' => Regency::inRandomOrder()->value('id'),
        'destinations' => Destination::inRandomOrder()->limit(fake()->numberBetween(1, 3))->pluck('id')->toArray(),
        'trip_date' => fake()->dateTimeBetween(today()->addWeek(), today()->addMonths(3)),
        'description' => '<p>' . fake()->text() . '</p>',
      ]);
    }
  }
}
