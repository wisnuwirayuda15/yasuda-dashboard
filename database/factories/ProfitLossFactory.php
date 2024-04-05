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
            'operational_costs' => '{}',
            'special_costs' => '{}',
            'variable_costs' => '{}',
            'other_costs' => '{}',
            'total_cost' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
