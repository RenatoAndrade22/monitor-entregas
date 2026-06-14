<?php

namespace App\Services;

use App\Models\Order;

class DriverPerformanceService
{
    public function getGlobalMetrics(): array
    {
        return [
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'deliveredOrders' => Order::where('status', 'delivered')->count(),
        ];
    }
}