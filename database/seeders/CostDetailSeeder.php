<?php

namespace Database\Seeders;

use App\Enums\CostDetailCategory;
use App\Models\CostDetail;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CostDetailSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $categories = array_map(fn($x) => $x->value, CostDetailCategory::cases());

    for ($x = 1; $x <= 20; $x++) {
      $category = fake()->randomElement($categories);

      CostDetail::create([
        'name' => ucwords(fake()->unique()->words(fake()->numberBetween(2, 3), true)),
        'price' => round(fake()->numberBetween(100000, 500000), -3),
        'cashback' => round(fake()->numberBetween(5000, 50000), -3),
        'category' => $category,
      ]);
    }
  }
}
