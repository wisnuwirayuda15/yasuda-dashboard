<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Invoice;
use App\Models\LoyaltyPoint;

class LoyaltyPointFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LoyaltyPoint::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'cash_status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'description' => $this->faker->text(),
            'amount' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
