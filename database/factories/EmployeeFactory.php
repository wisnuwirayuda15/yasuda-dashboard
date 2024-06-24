<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\User;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => $this->faker->word(),
            'name' => $this->faker->name(),
            'alias' => $this->faker->word(),
            'join_date' => $this->faker->dateTime(),
            'exit_date' => $this->faker->dateTime(),
            'ktp' => $this->faker->word(),
            'photo' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'gender' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'role' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
