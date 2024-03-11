<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Bu;
use App\Models\BusAvailability;

class BusAvailabilityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BusAvailability::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'bus_id' => Bu::factory(),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'date' => $this->faker->dateTime(),
        ];
    }
}
