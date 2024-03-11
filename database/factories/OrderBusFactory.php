<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\BusAvailability;
use App\Models\Order;
use App\Models\OrderBus;
use App\Models\OrderTourLeader;
use App\Models\TourLeader;

class OrderBusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderBus::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'tour_leader_id' => TourLeader::factory(),
            'bus_availability_id' => BusAvailability::factory(),
            'order_tour_leader_id' => OrderTourLeader::factory(),
        ];
    }
}
