<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\TourLeader;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Http;

class TourLeaderSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $genders = array_map(fn($x) => $x->value, Gender::cases());

    for ($x = 1; $x <= 10; $x++) {
      // $gender = fake()->randomElement($genders);
      $user = Http::get("https://randomuser.me/api/")->json()['results'];
      
      $gender = $user[0]['gender'];

      $photo = $user[0]['picture']['large'];

      TourLeader::create([
        'name' => fake()->name($gender),
        'photo' => $photo,
        'phone' => fake()->phoneNumber(),
        'gender' => $gender,
      ]);
    }
  }
}
