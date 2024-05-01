<?php

namespace Database\Seeders;

use App\Models\Fleet;
use App\Enums\OrderStatus;
use App\Models\OrderFleet;
use App\Models\TourLeader;
use Illuminate\Database\Seeder;
use App\Enums\FleetPaymentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderFleetSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    for ($i = 0; $i < 20; $i++) {
      OrderFleet::create([
        'code' => get_code(new OrderFleet, 'OF'),
        'fleet_id' => Fleet::inRandomOrder()->value('id'),
        'trip_date' => fake()->dateTimeBetween(today()->addWeek(), today()->addMonths(3)),
        'status' => OrderStatus::READY->value,
        'payment_status' => FleetPaymentStatus::NON_DP->value,
        'tour_leader_id' => TourLeader::inRandomOrder()->value('id'),
      ]);
    }
  }
}
