<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\District;
use App\Models\Regency;

class DistrictFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = District::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'regency_id' => Regency::factory(),
            'name' => $this->faker->name(),
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude(),
            'google_place_id' => $this->faker->word(),
        ];
    }
}
