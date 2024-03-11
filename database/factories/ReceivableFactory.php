<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Receivable;

class ReceivableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Receivable::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'creditor' => $this->faker->word(),
            'date' => $this->faker->dateTime(),
            'due_date' => $this->faker->dateTime(),
            'amount' => $this->faker->numberBetween(-100000, 100000),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
