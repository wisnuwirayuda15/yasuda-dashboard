<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Invoice;
use App\Models\Order;

class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'order_id' => Order::factory(),
            'main_costs' => '{}',
            'submitted_shirt' => $this->faker->numberBetween(-10000, 10000),
            'teacher_shirt_qty' => $this->faker->numberBetween(-10000, 10000),
            'adult_shirt_qty' => $this->faker->numberBetween(-10000, 10000),
            'child_shirt_price' => $this->faker->numberBetween(-100000, 100000),
            'teacher_shirt_price' => $this->faker->numberBetween(-100000, 100000),
            'adult_shirt_price' => $this->faker->numberBetween(-100000, 100000),
            'adjusted_seat' => $this->faker->numberBetween(-10000, 10000),
            'down_payments' => '{}',
            'other_cost' => $this->faker->numberBetween(-100000, 100000),
            'notes' => $this->faker->text(),
        ];
    }
}
