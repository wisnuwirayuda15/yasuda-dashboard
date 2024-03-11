<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\EmployeeSalary;
use App\Models\User;

class EmployeeSalaryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmployeeSalary::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->numberBetween(-100000, 100000),
            'date' => $this->faker->dateTime(),
            'working_day' => $this->faker->numberBetween(-10000, 10000),
        ];
    }
}
