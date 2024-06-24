<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\SalesVisit;

class SalesVisitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SalesVisit::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'employee_id' => Employee::factory(),
            'priority' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'visit_status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
