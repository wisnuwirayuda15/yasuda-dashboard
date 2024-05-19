<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Invoice;
use App\Models\ProfitLoss;

class ProfitLossFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProfitLoss::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'medium_rent_price' => $this->faker->numberBetween(-100000, 100000),
            'big_rent_price' => $this->faker->numberBetween(-100000, 100000),
            'legrest_rent_price' => $this->faker->numberBetween(-100000, 100000),
            'toll_price' => $this->faker->numberBetween(-100000, 100000),
            'banner_price' => $this->faker->numberBetween(-100000, 100000),
            'crew_price' => $this->faker->numberBetween(-100000, 100000),
            'tour_leader_price' => $this->faker->numberBetween(-100000, 100000),
            'documentation_qty' => $this->faker->numberBetween(-10000, 10000),
            'documentation_price' => $this->faker->numberBetween(-100000, 100000),
            'teacher_shirt_price' => $this->faker->numberBetween(-100000, 100000),
            'souvenir_price' => $this->faker->numberBetween(-100000, 100000),
            'child_shirt_price' => $this->faker->numberBetween(-100000, 100000),
            'adult_shirt_price' => $this->faker->numberBetween(-100000, 100000),
            'photo_price' => $this->faker->numberBetween(-100000, 100000),
            'snack_price' => $this->faker->numberBetween(-100000, 100000),
            'eat_price' => $this->faker->numberBetween(-100000, 100000),
            'backup_price' => $this->faker->numberBetween(-100000, 100000),
            'others_income' => $this->faker->numberBetween(-100000, 100000),
            'medium_subs_bonus' => $this->faker->numberBetween(-100000, 100000),
            'big_subs_bonus' => $this->faker->numberBetween(-100000, 100000),
            'legrest_subs_bonus' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
