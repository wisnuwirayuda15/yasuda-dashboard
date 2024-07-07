<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Fleet;
use App\Models\OrderFleet;
use App\Models\TourLeader;
use App\Enums\OrderFleetStatus;
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
    $user = User::first();

    for ($i = 0; $i < 100; $i++) {
      $model = OrderFleet::create([
        'code' => get_code(new OrderFleet, 'OF'),
        'fleet_id' => Fleet::withoutGlobalScopes()->inRandomOrder()->value('id'),
        'trip_date' => fake()->dateTimeBetween(today()->addWeek(), today()->addMonths(1)),
        // 'status' => OrderFleetStatus::READY->value,
        'payment_status' => FleetPaymentStatus::NON_DP->value,
        // 'tour_leader_id' => TourLeader::inRandomOrder()->value('id'),
      ]);

      $model->submit($user);
    }
  }
}
