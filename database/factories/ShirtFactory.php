<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Invoice;
use App\Models\Shirt;

class ShirtFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Shirt::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'child' => '{}',
            'adult' => '{}',
            'male_teacher' => '{}',
            'female_teacher' => '{}',
            'child_color' => $this->faker->word(),
            'adult_color' => $this->faker->word(),
            'male_teacher_color' => $this->faker->word(),
            'female_teacher_color' => $this->faker->word(),
            'child_sleeve' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'adult_sleeve' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'male_teacher_sleeve' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'female_teacher_sleeve' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'child_material' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'adult_material' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'male_teacher_material' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'female_teacher_material' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'total' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
