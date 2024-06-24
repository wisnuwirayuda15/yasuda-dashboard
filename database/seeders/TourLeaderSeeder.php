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
   * Retrieve an array of tour leaders with their details.
   *
   * @return array Array of tour leader details
   */
  public function getTourLeaders(): array
  {
    return [
      [
        'code' => "02/TLF/0001",
        'name' => "NANA ARIANATAMA",
        'alias' => "NANA",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0002",
        'name' => "ARIEF ARIE SANDHY",
        'alias' => "ARIF",
        'status' => "freelance",
        'gender' => Gender::FEMALE->value
      ],
      [
        'code' => "02/TLF/0003",
        'name' => "MUHAMMAD REZA",
        'alias' => "REZA",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0004",
        'name' => "ARDIAN TRI CAHYA",
        'alias' => "ARDIAN",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0005",
        'name' => "ADI KURNIAWAN",
        'alias' => "ADI K.",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0006",
        'name' => "ADI SENTANA",
        'alias' => "ADI S.",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0007",
        'name' => "MUHAMMAD NUR FAIZAL",
        'alias' => "FAIZAL",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0008",
        'name' => "SUDI RAHARJO",
        'alias' => "HARJO",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0009",
        'name' => "GOVINDA HERLAND",
        'alias' => "GOVINDA",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0010",
        'name' => "MIETHA",
        'alias' => "MIETHA",
        'status' => "freelance",
        'gender' => Gender::FEMALE->value
      ],
      [
        'code' => "02/TLF/0011",
        'name' => "NOFAN AZRIEL FALACH",
        'alias' => "OFAN",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ],
      [
        'code' => "02/TLF/0012",
        'name' => "RONNY KURNIAWAN",
        'alias' => "RONNY",
        'status' => "freelance",
        'gender' => Gender::MALE->value
      ]
    ];
  }

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    foreach (self::getTourLeaders() as $tr) {
      // $user = Http::get("https://randomuser.me/api/")->json()['results'];

      // $photo = $user[0]['picture']['large'];

      $photo = 'https://randomuser.me/api/portraits/' . ($tr['gender'] === Gender::MALE->value ? 'men' : 'women') . '/' . fake()->unique()->numberBetween(1, 99) . '.jpg';

      TourLeader::create([
        'code' => $tr['code'],
        'name' => $tr['name'],
        'alias' => $tr['alias'],
        'status' => $tr['status'],
        'photo' => $photo,
        'join_date' => today()->subDays(rand(0, 365)),
        'gender' => $tr['gender'],
      ]);
    }

    // $genders = enum_map(Gender::cases());
    // for ($x = 1; $x <= 10; $x++) {
    //   $gender = fake()->randomElement($genders);
    //   $user = Http::get("https://randomuser.me/api/")->json()['results'];

    //   $gender = $user[0]['gender'];

    //   $photo = $user[0]['picture']['large'];

    //   TourLeader::create([
    //     'name' => fake()->name($gender),
    //     'photo' => $photo,
    //     'phone' => fake()->numerify('+6281#########'),
    //     'gender' => $gender,
    //   ]);
    // }
  }
}
