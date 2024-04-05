<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TourLeader;
use App\Models\User;

class TourLeaderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TourLeader::class;

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
            'gender' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
