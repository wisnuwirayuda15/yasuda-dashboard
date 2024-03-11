<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Revenue;

class RevenueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Revenue::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_id' => Payment::factory(),
            'restaurant' => $this->faker->numberBetween(-100000, 100000),
            'souvenir' => $this->faker->numberBetween(-100000, 100000),
            'shirt' => $this->faker->numberBetween(-100000, 100000),
            'hotel' => $this->faker->numberBetween(-100000, 100000),
            'snack' => $this->faker->numberBetween(-100000, 100000),
            'catering' => $this->faker->numberBetween(-100000, 100000),
            'gross_income' => $this->faker->numberBetween(-100000, 100000),
            'net_income' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
