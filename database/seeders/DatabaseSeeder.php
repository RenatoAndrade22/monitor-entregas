<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\Order;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@teste.com',
            'password' => bcrypt('123456'),
        ]);

        // 15 motoristas com 100% das 50 entregas concluídas.
        Driver::factory(15)->create()->each(function ($driver) {
            Order::factory(50)->create([
                'driver_id' => $driver->id,
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        });

        // 20 motoristas com 35 concluídas e 15 pendentes
        Driver::factory(20)->create()->each(function ($driver) {
            Order::factory(35)->create([
                'driver_id' => $driver->id,
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            Order::factory(15)->create([
                'driver_id' => $driver->id,
                'status' => 'pending',
                'delivered_at' => null,
            ]);
        });

        // 15 motoristas com 15 concluídas e 35 pendentes
        Driver::factory(15)->create()->each(function ($driver) {
            Order::factory(15)->create([
                'driver_id' => $driver->id,
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            Order::factory(35)->create([
                'driver_id' => $driver->id,
                'status' => 'pending',
                'delivered_at' => null,
            ]);
        });
    }
}
