<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Destination;

class DestinationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Destination::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'type' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'marketing_name' => $this->faker->word(),
            'marketing_phone' => $this->faker->word(),
            'weekday_price' => $this->faker->numberBetween(-100000, 100000),
            'weekend_price' => $this->faker->numberBetween(-100000, 100000),
            'high_season_price' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
