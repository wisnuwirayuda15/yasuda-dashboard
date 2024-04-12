<?php

namespace Database\Seeders;

use App\Enums\CustomerCategory;
use App\Enums\CustomerStatus;
use App\Models\Regency;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CustomerSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $categories = array_map(fn($x) => $x->value, CustomerCategory::cases());

    $statuses = array_map(fn($x) => $x->value, CustomerStatus::cases());

    for ($x = 1; $x <= 100; $x++) {
      $category = fake()->randomElement($categories);

      $code = get_code(new Customer, $category . '-');

      $city = Regency::inRandomOrder()->first();

      $district = $city->districts()->inRandomOrder()->first();

      Customer::create([
        'code' => $code,
        'name' => "TK " . strtoupper(fake()->unique()->words(fake()->numberBetween(2, 4), true)),
        'address' => fake()->address(),
        'category' => $category,
        'regency_id' => $city->id,
        'district_id' => $district->id,
        'headmaster' => fake()->name(),
        'operator' => fake()->name(),
        'phone' => fake()->phoneNumber(),
        'email' => fake()->safeEmail(),
        'lat' => fake()->latitude(),
        'lng' => fake()->longitude(),
        'status' => fake()->randomElement($statuses),
      ]);
    }
  }
}
