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
            'down_payments' => '{}',
            'kaos_diserahkan' => $this->faker->numberBetween(-10000, 10000),
            'kaos_guru' => '{}',
            'kaos_dewasa' => '{}',
            'adjusted_seat' => $this->faker->numberBetween(-10000, 10000),
            'other_cost' => $this->faker->numberBetween(-100000, 100000),
            'notes' => $this->faker->text(),
            'total_transactions' => $this->faker->numberBetween(-100000, 100000),
        ];
    }
}
