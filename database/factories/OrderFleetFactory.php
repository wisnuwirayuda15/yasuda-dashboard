<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Fleet;
use App\Models\Order;
use App\Models\OrderFleet;
use App\Models\TourLeader;

class OrderFleetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderFleet::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'order_id' => Order::factory(),
            'fleet_id' => Fleet::factory(),
            'trip_date' => $this->faker->dateTime(),
            'payment_status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'payment_date' => $this->faker->dateTime(),
            'payment_amount' => $this->faker->numberBetween(-100000, 100000),
            'tour_leader_id' => TourLeader::factory(),
        ];
    }
}
