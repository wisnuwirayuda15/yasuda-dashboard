<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Order;
use App\Models\TourPackage;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'number_of_people' => $this->faker->numberBetween(-10000, 10000),
            'tour_package_id' => TourPackage::factory(),
            'payment_status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'banner_status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
        ];
    }
}
