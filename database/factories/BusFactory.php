<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Bus;

class BusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bus::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'image' => $this->faker->word(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'seat_total' => $this->faker->numberBetween(-10000, 10000),
            'type' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'price' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
