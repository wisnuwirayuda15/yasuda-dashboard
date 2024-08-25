<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

    $this->call($this->getSeeders());
  }

  protected function getSeeders(): array
  {
    $real = [
      ApprovalFlowSeeder::class,
      RegionDatabaseSeeder::class,
      EmployeeSeeder::class,
    ];

    // if ((bool) env('REGION_SEEDER', false)) {
    //   $real[] = RegionSeeder::class;
    // }

    $dummy = [
      FleetSeeder::class,
      CustomerSeeder::class,
      DestinationSeeder::class,
      OrderSeeder::class,
      OrderFleetSeeder::class,
      EventSeeder::class,
      ModelApprovalSeeder::class,
    ];

    return (bool) env('SEEDER_WITH_DUMMY_DATA', true)
      ? array_merge($real, $dummy)
      : $real;
  }
}

