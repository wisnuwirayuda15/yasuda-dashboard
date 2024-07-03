<?php

namespace Database\Seeders;

use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
        'status' => EmployeeStatus::PERMANENT->value,
        'gender' => Gender::MALE->value,
        'ktp' => "3328160510710002",
        'join_date' => now()->setDate(2008, 9, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0002",
        'name' => "IDA SUSANTI",
        'alias' => "IDA",
        'role' => EmployeeRole::FINANCE_STAFF->value,
        'status' => EmployeeStatus::PERMANENT->value,
        'gender' => Gender::FEMALE->value,
        'ktp' => "3328165611780002",
        'join_date' => now()->setDate(2008, 9, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0003",
        'name' => "SUPRAPTO",
        'alias' => "ATO",
        'role' => EmployeeRole::MARKETING_STAFF->value,
        'status' => EmployeeStatus::PERMANENT->value,
        'gender' => Gender::MALE->value,
        'ktp' => "3328161204850003",
        'join_date' => now()->setDate(2009, 1, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0004",
        'name' => "AMIN MUSHOLIN",
        'alias' => "AMIN",
        'role' => EmployeeRole::MARKETING_STAFF->value,
        'status' => EmployeeStatus::PERMANENT->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'join_date' => now()->setDate(2016, 1, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0005",
        'name' => "RIZAL AFFANDI",
        'alias' => "RIZAL",
        'role' => EmployeeRole::MARKETING_STAFF->value,
        'status' => EmployeeStatus::PERMANENT->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'join_date' => now()->setDate(2020, 1, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],
      [
        'code' => "01/YSD/0006",
        'name' => "SYAHRUL ILYASA",
        'alias' => "SYAHRUL",
        'role' => EmployeeRole::MANAGER->value,
        'status' => EmployeeStatus::PERMANENT->value,
        'gender' => Gender::MALE->value,
        'ktp' => "3328160407970001",
        'join_date' => now()->setDate(2023, 5, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
      ],

      // Tour Leader
      [
        'name' => "NANA ARIANATAMA",
        'alias' => "NANA",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "ARIEF ARIE SANDHY",
        'alias' => "ARIF",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::FEMALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "MUHAMMAD REZA",
        'alias' => "REZA",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "ARDIAN TRI CAHYA",
        'alias' => "ARDIAN",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "ADI KURNIAWAN",
        'alias' => "ADI K.",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "ADI SENTANA",
        'alias' => "ADI S.",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "MUHAMMAD NUR FAIZAL",
        'alias' => "FAIZAL",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "SUDI RAHARJO",
        'alias' => "HARJO",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "GOVINDA HERLAND",
        'alias' => "GOVINDA",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "MIETHA",
        'alias' => "MIETHA",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::FEMALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "NOFAN AZRIEL FALACH",
        'alias' => "OFAN",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ],
      [
        'name' => "RONNY KURNIAWAN",
        'alias' => "RONNY",
        'status' => EmployeeStatus::FREELANCE->value,
        'gender' => Gender::MALE->value,
        'ktp' => null,
        'role' => EmployeeRole::TOUR_LEADER->value,
        'join_date' => null,
      ]
    ];
  }

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    foreach (static::getEmployees() as $employee) {
      $employee['photo'] = 'https://randomuser.me/api/portraits/' . ($employee['gender'] === Gender::MALE->value ? 'men' : 'women') . '/' . fake()->unique()->numberBetween(1, 99) . '.jpg';

      $employee['code'] = emp_code(new Employee, $employee['role'] === EmployeeRole::TOUR_LEADER->value ? '02/TLF/' : '01/YSD/');

      $employee['join_date'] = $employee['join_date'] ?? today()->subDays(rand(0, 365));

      Employee::create($employee);
    }
  }
}
