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
    $categories = enum_map(CustomerCategory::cases());

    $statuses = enum_map(CustomerStatus::cases());

    for ($x = 1; $x <= 500; $x++) {
      $category = fake()->randomElement($categories);

      $status = fake()->randomElement($statuses);

      $code = get_code(new Customer, $category);

      $city = Regency::inRandomOrder()->first();

      $district = $city->districts()->inRandomOrder()->first();

      $name = ($category === CustomerCategory::UMUM->value ? '' : strtoupper($category) . " ") . strtoupper(fake()->unique()->words(fake()->numberBetween(2, 4), true));

      Customer::create([
        'code' => $code,
        'name' => $name,
        'address' => fake()->address(),
        'category' => $category,
        'regency_id' => $city->id,
        'district_id' => $district->id,
        'headmaster' => fake()->name(),
        'operator' => fake()->name(),
        'phone' => fake()->numerify('+6281#########'),
        'email' => fake()->safeEmail(),
        'lat' => fake()->latitude(),
        'lng' => fake()->longitude(),
        'status' => $status,
      ]);
    }
  }
}
