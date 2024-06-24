<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Database\Seeders\FleetSeeder;
use Database\Seeders\CostDetailSeeder;
use Database\Seeders\DestinationSeeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    User::factory()->create([
      'name' => 'Super Admin',
      'email' => 'superadmin@yasuda.com',
      'password' => bcrypt('12345678'),
    ]);

    Company::create();
    
    $this->call([
      RegionSeeder::class,
      FleetSeeder::class,
      CustomerSeeder::class,
      TourLeaderSeeder::class,
      DestinationSeeder::class,
      OrderSeeder::class,
      OrderFleetSeeder::class,
      EmployeeSeeder::class,
    ]);
  }
}
