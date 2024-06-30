<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Reward;

class RewardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reward::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'cash_status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'date' => $this->faker->dateTime(),
            'description' => $this->faker->text(),
            'amount' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
