@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pages/dashboard.css') }}">

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="font-weight-bold text-dark mb-0">Dashboard - Panorama de Operação</h4>
    </div>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 card-gradient-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small font-weight-bold mb-1">Total de Pedidos</div>
                            <h2 class="font-weight-bold mb-0">{{ $totalOrders }}</h2>
                        </div>
                        <div><i class="fas fa-boxes fa-2x opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 card-gradient-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small font-weight-bold mb-1">Entregues com Sucesso</div>
                            <h2 class="font-weight-bold mb-0">{{ $deliveredOrders }}</h2>
                        </div>
                        <div><i class="fas fa-check-circle fa-2x opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 card-gradient-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small font-weight-bold mb-1">Pendentes</div>
                            <h2 class="font-weight-bold mb-0">{{ $pendingOrders }}</h2>
                        </div>
                        <div><i class="fas fa-clock fa-2x opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-chart-pie text-secondary mr-2"></i> Distribuição de Status das Entregas</h6>
                </div>
                <div class="card-body chart-container">
                    <canvas id="myPieChart" data-delivered="{{ $deliveredOrders }}" data-pending="{{ $pendingOrders }}"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/pages/dashboard.js') }}"></script>
@endsection