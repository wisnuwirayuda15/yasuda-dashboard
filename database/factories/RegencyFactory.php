<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Province;
use App\Models\Regency;

class RegencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Regency::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'province_id' => Province::factory(),
            'name' => $this->faker->name(),
        ];
    }
}
