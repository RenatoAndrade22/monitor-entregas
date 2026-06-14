@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="{{ asset('css/pages/drivers.css') }}">

<div class="container-fluid px-4 py-3">
    <h4 class="mb-3 font-weight-bold text-dark">Painel de Desempenho - Motoristas</h4>
    <p class="text-muted small mb-4">Acompanhamento analítico do volume de entregas e taxas de sucesso por colaborador.</p>

    <div class="card shadow-sm border-0 mb-4 rounded-lg">
        <div class="card-body py-3">
            <h6 class="font-weight-bold mb-3" style="color: #2c3e50;"><i class="fas fa-filter mr-2"></i>Filtros</h6>
            
            <form method="GET" action="{{ route('motoristas') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="text-muted small font-weight-bold">Data (Período)</label>
                        <input type="text" class="form-control form-control-sm custom-input" name="daterange" id="daterange" value="{{ request('daterange') }}" autocomplete="off">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="text-muted small font-weight-bold">Status de Desempenho</label>
                        <select class="form-control form-control-sm custom-input" name="status" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Todos</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>🟢 Concluído</option>
                            <option value="almost" {{ request('status') == 'almost' ? 'selected' : '' }}>🟠 Próximo de terminar</option>
                            <option value="alert" {{ request('status') == 'alert' ? 'selected' : '' }}>🔴 Em alerta</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-lg overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-white">
                <div class="text-muted small">
                    Mostrando {{ $drivers->firstItem() ?? 0 }}-{{ $drivers->lastItem() ?? 0 }} de {{ $drivers->total() }}
                </div>
                <div>{{ $drivers->links('pagination::bootstrap-4') }}</div>
                <div>
                    <a href="{{ route('motoristas') }}" class="btn btn-outline-secondary btn-sm px-3 custom-btn-outline" title="Limpar Filtros">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-top-0 text-dark font-weight-bold pl-4" style="width: 45%;">Nome do Motorista</th>
                            <th class="border-top-0 text-dark font-weight-bold">Total de Pedidos</th>
                            <th class="border-top-0 text-dark font-weight-bold">Entregues com Sucesso</th>
                            <th class="border-top-0 text-dark font-weight-bold pr-4">Desempenho</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            @php
                                $colorClass = 'row-danger';
                                if ($driver->performance_percentage == 100) {
                                    $colorClass = 'row-success';
                                } elseif ($driver->performance_percentage > 50) {
                                    $colorClass = 'row-warning';
                                }
                            @endphp

                            <tr class="{{ $colorClass }}">
                                <td class="text-secondary align-middle font-weight-bold pl-4">{{ $driver->name }}</td>
                                <td class="align-middle">
                                    <button type="button" class="btn btn-link font-weight-bold text-dark p-0 btn-modal-trigger open-orders-modal" data-id="{{ $driver->id }}" data-name="{{ $driver->name }}">
                                        {{ $driver->total_orders }}
                                    </button>
                                </td>
                                <td class="align-middle">
                                    <button type="button" class="btn btn-link font-weight-bold p-0 btn-modal-trigger open-delivered-modal" style="color: #28a745;" data-id="{{ $driver->id }}" data-name="{{ $driver->name }}">
                                        {{ $driver->delivered_orders }}
                                    </button>
                                </td>
                                <td class="align-middle font-weight-bold pr-4">
                                    {{ $driver->performance_percentage }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Nenhum motorista encontrado com os filtros selecionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="globalOrdersModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form action="{{ route('motoristas.orders.updateBulk') }}" method="POST" class="w-100" id="modalOrdersForm">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title font-weight-bold" style="color: #2c3e50; font-size: 1.15rem;">
                        Pedidos - <span id="modalOrdersDriverName"></span>
                    </h5>
                    <button type="button" class="close" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body px-4 pt-2">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div class="text-muted small" id="ordersCounterInfo">Carregando...</div>
                        <div id="ordersPaginationControls"></div>
                    </div>

                    <div class="table-responsive" style="max-height: 450px; min-height: 200px; overflow-y: auto;">
                        <table class="table table-borderless mb-0 custom-modal-table">
                            <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                                <tr>
                                    <th style="width: 20%;" class="pl-2">Código do Pedido</th>
                                    <th style="width: 60%;">Endereço</th>
                                    <th style="width: 20%;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 mt-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light custom-btn-cancel">Cancelar</button>
                    <button type="submit" class="btn custom-btn-save"><i class="fas fa-save mr-2"></i>Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="globalDeliveredModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form action="{{ route('motoristas.orders.updateBulk') }}" method="POST" class="w-100" id="modalDeliveredForm">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title font-weight-bold text-success" style="font-size: 1.15rem;">
                        Entregas Concluídas - <span id="modalDeliveredDriverName"></span>
                    </h5>
                    <button type="button" class="close" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body px-4 pt-2">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div class="text-muted small" id="deliveredCounterInfo">Carregando...</div>
                        <div id="deliveredPaginationControls"></div>
                    </div>

                    <div class="table-responsive" style="max-height: 450px; min-height: 200px; overflow-y: auto;">
                        <table class="table table-borderless mb-0 custom-modal-table">
                            <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                                <tr>
                                    <th style="width: 20%;" class="pl-2">Código do Pedido</th>
                                    <th style="width: 50%;">Endereço</th>
                                    <th style="width: 30%;">Data e Horário da Entrega</th>
                                </tr>
                            </thead>
                            <tbody id="deliveredTableBody">
                                </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 mt-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light custom-btn-cancel">Cancelar</button>
                    <button type="submit" class="btn custom-btn-save"><i class="fas fa-save mr-2"></i>Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/pages/drivers.js') }}"></script>
@endsection