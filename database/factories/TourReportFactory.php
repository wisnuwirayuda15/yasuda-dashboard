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
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'costs_detail' => '{}',
            'other_costs' => '{}',
            'income' => $this->faker->numberBetween(-100000, 100000),
            'expense' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
