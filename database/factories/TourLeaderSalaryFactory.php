<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TourLeader;
use App\Models\TourLeaderSalary;

class TourLeaderSalaryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TourLeaderSalary::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'tour_leader_id' => TourLeader::factory(),
            'amount' => $this->faker->numberBetween(-100000, 100000),
            'date' => $this->faker->dateTime(),
        ];
    }
}
