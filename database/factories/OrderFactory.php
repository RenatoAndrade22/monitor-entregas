<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Driver;

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
     *
     * @return array
     */
    public function definition()
    {
        $status = $this->faker->randomElement(['pending', 'delivered']);
    
        return [
            'driver_id' => Driver::factory(), 
            'code' => $this->faker->unique()->numerify('########'),
            'delivery_address' => $this->faker->address(),
            'status' => $status,
            'delivered_at' => $status == 'delivered' ? now() : null,
        ];
    }
}
