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
            'name' => $this->faker->name(),
            'photo' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->word(),
            'role' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
