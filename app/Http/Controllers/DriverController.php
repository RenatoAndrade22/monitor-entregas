<?php

namespace App\Http\Controllers;

use App\Services\DriverService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    private $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->middleware('auth');
        $this->driverService = $driverService;
    }

    public function index(Request $request)
    {
        $drivers = $this->driverService->getPaginatedPerformance(10, $request->all());
        return view('Pages.Drivers.drivers', compact('drivers'));
    }

    /**
     * Endpoint API para chamadas AJAX do Modal
     */
    public function getOrders(Request $request, $id)
    {
        $status = $request->query('status'); 
        $orders = $this->driverService->getDriverOrdersPaginated($id, 10, $status);

        return response()->json($orders);
    }

    /**
     * Recebe os pedidos modificados e manda para o Service salvar
     */
    public function updateBulkOrders(Request $request)
    {
        $orders = $request->input('orders', []);
        
        if (!empty($orders)) {
            $this->driverService->updateBulkOrders($orders);
        }

        return response()->json(['success' => true, 'message' => 'Pedidos atualizados com sucesso.']);
    }
}