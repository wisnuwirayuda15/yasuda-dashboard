<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Regency;
use App\Models\TourTemplate;

class TourTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TourTemplate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'image' => $this->faker->word(),
            'name' => $this->faker->name(),
            'regency_id' => Regency::factory(),
            'destinations' => '{}',
            'description' => $this->faker->text(),
        ];
    }
}
