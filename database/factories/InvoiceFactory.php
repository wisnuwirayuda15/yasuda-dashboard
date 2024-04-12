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
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'costs_detail' => '{}',
            'special_notes' => $this->faker->text(),
        ];
    }
}
