<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Incident;

class IncidentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Incident::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'date' => $this->faker->dateTime(),
            'description' => $this->faker->text(),
            'loss' => $this->faker->numberBetween(-100000, 100000),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
