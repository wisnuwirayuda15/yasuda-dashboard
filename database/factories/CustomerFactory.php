<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\District;
use App\Models\Regency;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'name' => $this->faker->name(),
            'address' => $this->faker->word(),
            'category' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'regency_id' => Regency::factory(),
            'district_id' => District::factory(),
            'headmaster' => $this->faker->word(),
            'operator' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude(),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'loyalty_point' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
