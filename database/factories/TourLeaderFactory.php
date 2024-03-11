<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\OrderBus;
use App\Models\TourLeader;

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
            'name' => $this->faker->name(),
            'photo' => $this->faker->word(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'gender' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'address' => $this->faker->word(),
            'order_bus_id' => OrderBus::factory(),
        ];
    }
}
