<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Fleet;

class FleetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Fleet::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'image' => $this->faker->word(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'category' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'seat_set' => $this->faker->numberBetween(-8, 8),
            'pic_name' => $this->faker->word(),
            'pic_phone' => $this->faker->word(),
        ];
    }
}
