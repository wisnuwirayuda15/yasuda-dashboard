<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Fleet;
use App\Models\Order;
use App\Models\Company;
use App\Models\OrderFleet;
use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    User::factory()->create([
      'name' => 'Super Admin',
      'email' => env('ADMIN_EMAIL', 'admin@example.com'),
      'password' => Hash::make(env('ADMIN_PASSWORD', '12345678')),
    ]);

    // Company::create();

    $realSeeders = [
      ApprovalFlowSeeder::class,
      RegionSeeder::class,
      EmployeeSeeder::class,
    ];

    $dummySeeders = [
      FleetSeeder::class,
      CustomerSeeder::class,
      DestinationSeeder::class,
      OrderSeeder::class,
      OrderFleetSeeder::class,
      EventSeeder::class,
      ModelApprovalSeeder::class,
    ];

    if ((bool) env('SEEDER_WITH_DUMMY_DATA', true)) {
      $seeders = array_merge($realSeeders, $dummySeeders);
    } else {
      $seeders = $realSeeders;
    }

    $this->call($seeders);
  }
}
