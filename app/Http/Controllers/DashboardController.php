<?php

namespace App\Http\Controllers;

use App\Services\DriverPerformanceService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $performanceService;

    public function __construct(DriverPerformanceService $performanceService)
    {
        $this->middleware('auth');
        $this->performanceService = $performanceService;
    }

    public function index()
    {
        $metrics = $this->performanceService->getGlobalMetrics();
        
        $totalOrders = $metrics['totalOrders'];
        $pendingOrders = $metrics['pendingOrders'];
        $deliveredOrders = $metrics['deliveredOrders'];

        return view('Pages.Dashboard.dashboard', compact('totalOrders', 'pendingOrders', 'deliveredOrders'));
    }
}