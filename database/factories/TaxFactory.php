<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Tax;

class TaxFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tax::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'institution' => $this->faker->word(),
            'date' => $this->faker->dateTime(),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'amount' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
