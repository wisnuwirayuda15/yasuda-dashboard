<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Brochure;

class BrochureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Brochure::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'institution' => $this->faker->word(),
            'category' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'date' => $this->faker->dateTime(),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
