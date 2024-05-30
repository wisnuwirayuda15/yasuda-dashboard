<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Invoice;
use App\Models\TourReport;

class TourReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TourReport::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'main_costs' => '{}',
            'other_costs' => '{}',
            'customer_repayment' => $this->faker->numberBetween(-100000, 100000),
            'difference' => $this->faker->numberBetween(-100000, 100000),
            'income_total' => $this->faker->numberBetween(-100000, 100000),
            'expense_total' => $this->faker->numberBetween(-100000, 100000),
            'defisit_surplus' => $this->faker->numberBetween(-100000, 100000),
            'refundable' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
