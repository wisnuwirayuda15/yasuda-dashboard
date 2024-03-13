<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Support\Str;
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

    // $this->call([
    //   RegionSeeder::class,
    // ]);
  }
}
