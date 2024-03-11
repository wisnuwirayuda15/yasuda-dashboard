<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Payment;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => $this->faker->numberBetween(-100000, 100000),
            'total' => $this->faker->numberBetween(-100000, 100000),
            'method' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
