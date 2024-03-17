<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Bus;
use App\Models\Customer;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // \App\Models\User::factory(10)->create();

    User::factory()->create([
      'name' => 'Super Admin',
      'email' => 'superadmin@yasuda.com',
      'password' => bcrypt('12345678'),
    ]);
    
    $this->call([
      RegionSeeder::class,
    ]);

    Bus::factory(50)->create();

    TourPackage::factory(15)->create();

    Customer::factory(100)->create();

  }
}
