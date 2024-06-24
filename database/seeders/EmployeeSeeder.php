<?php

namespace Database\Seeders;

use App\Enums\EmployeeRole;
use App\Enums\Gender;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Http;

class EmployeeSeeder extends Seeder
{
  /**
   * Get the list of employees with their details.
   *
   * @return array The array containing employee details.
   */
  public function getEmployees(): array
  {
    return [
      // Employee
      [
        'code' => "01/YSD/0001",
        'name' => "RIDWAN",
        'alias' => "RIDWAN",
        'role' => EmployeeRole::MARKETING_MANAGER->value,
        'status' => 'permanent',
        'gender' => Gender::MALE->value,
        'ktp' => "3328160510710002",
        'join_date' => now()->setDate(2008, 9, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0002",
        'name' => "IDA SUSANTI",
        'alias' => "IDA",
        'role' => EmployeeRole::FINANCE_STAFF->value,
        'status' => 'permanent',
        'gender' => Gender::FEMALE->value,
        'ktp' => "3328165611780002",
        'join_date' => now()->setDate(2008, 9, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0003",
        'name' => "SUPRAPTO",
        'alias' => "ATO",
        'role' => EmployeeRole::MARKETING_STAFF->value,
        'status' => 'permanent',
        'gender' => Gender::MALE->value,
        'ktp' => "3328161204850003",
        'join_date' => now()->setDate(2009, 1, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0004",
        'name' => "AMIN MUSHOLIN",
        'alias' => "AMIN",
        'role' => EmployeeRole::MARKETING_STAFF->value,
        'status' => 'permanent',
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'join_date' => now()->setDate(2016, 1, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0005",
        'name' => "RIZAL AFFANDI",
        'alias' => "RIZAL",
        'role' => EmployeeRole::MARKETING_STAFF->value,
        'status' => 'permanent',
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'join_date' => now()->setDate(2020, 1, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0006",
        'name' => "SYAHRUL ILYASA",
        'alias' => "SYAHRUL",
        'role' => EmployeeRole::MANAGER->value,
        'status' => 'permanent',
        'gender' => Gender::MALE->value,
        'ktp' => "3328160407970001",
        'join_date' => now()->setDate(2023, 5, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],

      // Tour Leader
      // [
      //   'code' => "02/TLF/0001",
      //   'name' => "NANA ARIANATAMA",
      //   'alias' => "NANA",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0002",
      //   'name' => "ARIEF ARIE SANDHY",
      //   'alias' => "ARIF",
      //   'status' => "freelance",
      //   'gender' => Gender::FEMALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0003",
      //   'name' => "MUHAMMAD REZA",
      //   'alias' => "REZA",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0004",
      //   'name' => "ARDIAN TRI CAHYA",
      //   'alias' => "ARDIAN",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0005",
      //   'name' => "ADI KURNIAWAN",
      //   'alias' => "ADI K.",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0006",
      //   'name' => "ADI SENTANA",
      //   'alias' => "ADI S.",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0007",
      //   'name' => "MUHAMMAD NUR FAIZAL",
      //   'alias' => "FAIZAL",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0008",
      //   'name' => "SUDI RAHARJO",
      //   'alias' => "HARJO",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0009",
      //   'name' => "GOVINDA HERLAND",
      //   'alias' => "GOVINDA",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0010",
      //   'name' => "MIETHA",
      //   'alias' => "MIETHA",
      //   'status' => "freelance",
      //   'gender' => Gender::FEMALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0011",
      //   'name' => "NOFAN AZRIEL FALACH",
      //   'alias' => "OFAN",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ],
      // [
      //   'code' => "02/TLF/0012",
      //   'name' => "RONNY KURNIAWAN",
      //   'alias' => "RONNY",
      //   'status' => "freelance",
      //   'gender' => Gender::MALE->value,
      //   'ktp' => null,
      //   'role' => EmployeeRole::TOUR_LEADER->value,
      //   'join_date' => null,
      // ]
    ];
  }

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    foreach (self::getEmployees() as $employee) {
      // $user = Http::get("https://randomuser.me/api/")->json()['results'];

      // $photo = $user[0]['picture']['large'];

      $photo = 'https://randomuser.me/api/portraits/' . ($employee['gender'] === Gender::MALE->value ? 'men' : 'women') . '/' . fake()->unique()->numberBetween(1, 99) . '.jpg';

      Employee::create([
        'photo' => $photo,
        'code' => $employee['code'],
        'name' => $employee['name'],
        'alias' => $employee['alias'],
        'status' => $employee['status'],
        'role' => $employee['role'],
        'ktp' => $employee['ktp'],
        'join_date' => $employee['join_date'] ?? today()->subDays(rand(0, 365)),
        'gender' => $employee['gender'],
      ]);
    }
  }
}
