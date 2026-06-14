<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DriverService
{
    /**
     * Retorna a listagem de performance dos motoristas.
     */
    public function getPaginatedPerformance(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $dates = $this->extractDateRange($filters['daterange'] ?? null);
        
        $query = $this->buildPerformanceQuery($dates);
        $query = $this->applyStatusFilter($query, $filters['status'] ?? null);

        $drivers = $query->paginate($perPage)->appends($filters);

        return $this->calculatePerformancePercentages($drivers);
    }

    /**
     * Busca os pedidos de um motorista específico de forma paginada para o Modal.
     */
    public function getDriverOrdersPaginated(int $driverId, int $perPage = 10, ?string $statusFilter = null): LengthAwarePaginator
    {
        return Order::where('driver_id', $driverId)
            ->when($statusFilter, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Atualiza em lote apenas os pedidos que foram modificados no modal.
     */
    public function updateBulkOrders(array $editedOrders): void
    {
        foreach ($editedOrders as $orderId => $data) {
            $this->processSingleOrderUpdate($orderId, $data);
        }
    }

    private function extractDateRange(?string $dateRange): array
    {
        if (!$dateRange) {
            return ['start' => null, 'end' => null];
        }

        $dates = explode(' - ', $dateRange);
        
        if (count($dates) !== 2) {
            return ['start' => null, 'end' => null];
        }

        return [
            'start' => Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay()->toDateTimeString(),
            'end'   => Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay()->toDateTimeString()
        ];
    }

    private function buildPerformanceQuery(array $dates): Builder
    {
        return Driver::withCount([
            'orders as total_orders' => fn ($q) => $this->applyDateFilterToRelation($q, $dates),
            'orders as delivered_orders' => fn ($q) => $this->applyDateFilterToRelation($q->where('status', 'delivered'), $dates)
        ]);
    }

    private function applyDateFilterToRelation($query, array $dates)
    {
        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('created_at', [$dates['start'], $dates['end']]);
        }
        return $query;
    }

    private function applyStatusFilter(Builder $query, ?string $status): Builder
    {
        switch ($status) {
            case 'completed':
                return $query->havingRaw('total_orders > 0 AND delivered_orders = total_orders');
            case 'almost':
                return $query->havingRaw('total_orders > 0 AND (delivered_orders / total_orders) > 0.5 AND delivered_orders < total_orders');
            case 'alert':
                return $query->havingRaw('total_orders = 0 OR (delivered_orders / total_orders) <= 0.5');
            default:
                return $query;
        }
    }

    private function calculatePerformancePercentages(LengthAwarePaginator $drivers): LengthAwarePaginator
    {
        $drivers->getCollection()->transform(function ($driver) {
            $driver->performance_percentage = $driver->total_orders > 0 
                ? round(($driver->delivered_orders / $driver->total_orders) * 100, 2) 
                : 0;
            return $driver;
        });

        return $drivers;
    }

    private function processSingleOrderUpdate(int $orderId, array $data): void
    {
        $order = Order::find($orderId);
            
        if (!$order) {
            Log::warning("Ordem com ID #{$orderId} não encontrada no banco de dados.");
            return;
        }
        
        if (isset($data['address'])) {
            $order->delivery_address = $data['address'];
        }
        
        if (isset($data['status'])) {
            $this->updateOrderStatus($order, $data['status']);
        }
        
        $order->save();
    }

    private function updateOrderStatus(Order $order, string $status): void
    {
        $order->status = $status;
        
        if ($status === 'delivered') {
            $order->delivered_at = $order->delivered_at ?? Carbon::now();
        } else {
            $order->delivered_at = null; 
        }
    }
}