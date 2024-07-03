<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Database\Seeders\FleetSeeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\DestinationSeeder;
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
      'email' => 'superadmin@yasuda.com',
      'password' => Hash::make('12345678'),
    ]);

    Company::create();
    
    $this->call([
      RegionSeeder::class,
      FleetSeeder::class,
      CustomerSeeder::class,
      DestinationSeeder::class,
      OrderSeeder::class,
      OrderFleetSeeder::class,
      EmployeeSeeder::class,
    ]);
  }
}
