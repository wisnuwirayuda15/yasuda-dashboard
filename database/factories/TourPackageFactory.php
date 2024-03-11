<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TourPackage;

class TourPackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TourPackage::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'image' => $this->faker->word(),
            'name' => $this->faker->name(),
            'city' => $this->faker->city(),
            'description' => $this->faker->text(),
            'order_total' => $this->faker->numberBetween(-10000, 10000),
            'price' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
