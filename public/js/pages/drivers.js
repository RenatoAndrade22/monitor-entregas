const DriverPerformancePanel = {
    // 1. Estado da Aplicação
    state: {
        activeDriverId: null,
        activeStatusFilter: null,
        editedOrders: {}
    },

    // 2. Método de Inicialização
    init: function() {
        this.initDateRangePicker();
        this.bindModalTriggers();
        this.bindPaginationControls();
        this.bindSaveForm();
        this.forceModalCloseHandlers();
    },

    // 3. Inicializa o calendário de filtros
    initDateRangePicker: function() {
        const $daterange = $('#daterange');
        
        $daterange.daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Limpar',
                daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']
            }
        });

        // Se o campo já veio preenchido pelo backend, aplicamos o valor
        if ($daterange.val()) {
            $daterange.val($daterange.val());
        }

        $daterange.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            $('#filterForm').submit();
        });
    },

    // 4. Força o fechamento via jQuery para evitar bugs de DOM do Bootstrap
    forceModalCloseHandlers: function() {
        $('#globalOrdersModal, #globalDeliveredModal').on('click', '.close, .custom-btn-cancel', function(e) {
            e.preventDefault();
            $(this).closest('.modal').modal('hide');
        });
    },

    // 5. Escuta cliques na tabela principal para abrir os modais
    bindModalTriggers: function() {
        const self = this;

        $('.open-orders-modal').click(function() {
            self.state.activeDriverId = $(this).data('id');
            self.state.activeStatusFilter = null; // Todos os status
            self.state.editedOrders = {}; 
            
            $('#modalOrdersDriverName').text($(this).data('name'));
            self.fetchModalData(1, 'orders');
            $('#globalOrdersModal').modal('show');
        });

        $('.open-delivered-modal').click(function() {
            self.state.activeDriverId = $(this).data('id');
            self.state.activeStatusFilter = 'delivered'; // Somente entregues
            self.state.editedOrders = {}; 
            
            $('#modalDeliveredDriverName').text($(this).data('name'));
            self.fetchModalData(1, 'delivered');
            $('#globalDeliveredModal').modal('show');
        });
    },

    // 6. Delegação de eventos de paginação do modal
    bindPaginationControls: function() {
        const self = this;
        $(document).on('click', '.modal-page-link', function(e) {
            e.preventDefault();
            let targetPage = $(this).data('page');
            let type = $(this).closest('.modal').attr('id') === 'globalOrdersModal' ? 'orders' : 'delivered';
            self.fetchModalData(targetPage, type);
        });
    },

    // 7. Busca os dados na API e renderiza a tabela do modal
    fetchModalData: function(page, type) {
        const self = this;
        let bodySelector = type === 'orders' ? '#ordersTableBody' : '#deliveredTableBody';
        let counterSelector = type === 'orders' ? '#ordersCounterInfo' : '#deliveredCounterInfo';
        let controlsSelector = type === 'orders' ? '#ordersPaginationControls' : '#deliveredPaginationControls';

        $(bodySelector).html('<tr><td colspan="3" class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i> Carregando registros...</td></tr>');

        let url = `/motoristas/${this.state.activeDriverId}/pedidos?page=${page}`;
        if (this.state.activeStatusFilter) {
            url += `&status=${this.state.activeStatusFilter}`;
        }

        $.getJSON(url, function(response) {
            $(bodySelector).empty();
            
            let from = response.from ?? 0;
            let to = response.to ?? 0;
            let total = response.total;
            $(counterSelector).html(`Mostrando ${from}-${to} de ${total}`);

            // Constrói Paginação
            let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';
            if (response.links) {
                $.each(response.links, function(index, link) {
                    let pageNum = '1';
                    if (link.url) {
                        let match = link.url.match(/page=(\d+)/);
                        if (match) pageNum = match[1];
                    }

                    let label = String(link.label);
                    if (label.includes('&laquo;') || label.includes('Previous') || label.includes('lsaquo')) {
                        label = '&lt;'; 
                    } else if (label.includes('&raquo;') || label.includes('Next') || label.includes('rsaquo')) {
                        label = '&gt;';
                    }

                    if (link.url === null) {
                        paginationHtml += `<li class="page-item disabled"><span class="page-link text-muted border-0 bg-transparent">${label}</span></li>`;
                    } else if (link.active) {
                        paginationHtml += `<li class="page-item active"><span class="page-link border-0 rounded bg-light text-dark font-weight-bold">${label}</span></li>`;
                    } else {
                        paginationHtml += `<li class="page-item"><a class="page-link modal-page-link border-0 text-secondary" href="#" data-page="${pageNum}">${label}</a></li>`;
                    }
                });
            }
            paginationHtml += '</ul></nav>';
            $(controlsSelector).html(paginationHtml);

            if(response.data.length === 0) {
                $(bodySelector).html(`<tr><td colspan="3" class="text-center py-5 text-muted">Nenhum registro encontrado.</td></tr>`);
                return;
            }

            // Constrói as Linhas
            $.each(response.data, function(index, order) {
                let rowHtml = '';
                
                let editedData = self.state.editedOrders[order.id] || {};
                let currentStatus = editedData.status || order.status;
                let currentAddress = editedData.address || order.delivery_address;
                
                if (type === 'orders') {
                    let statusLabel = currentStatus === 'delivered' ? 'Entregue' : 'Pendente';
                    
                    rowHtml = `
                        <tr class="border-bottom" style="border-bottom-color: #f1f5f9 !important;">
                            <td class="align-middle pl-2 font-weight-bold" style="color: #475569;">#${order.code}</td>
                            <td class="align-middle address-cell">
                                <span class="display-address" style="cursor: pointer; color: #475569;">
                                    <span class="address-text-${order.id}">${currentAddress}</span> <i class="fas fa-pencil-alt fa-xs edit-icon" title="Editar"></i>
                                </span>
                                <input type="text" class="form-control form-control-sm edit-input custom-input" name="orders[${order.id}][address]" data-id="${order.id}" value="${currentAddress}" style="display: none;">
                            </td>
                            <td class="align-middle pr-2">
                                <div class="dropdown custom-status-dropdown">
                                    <button class="btn btn-sm btn-block text-left shadow-none dropdown-toggle p-0 font-weight-bold" type="button" data-toggle="dropdown" style="color: #475569;">
                                        <span class="status-label-${order.id}">${statusLabel}</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="border-radius: 8px;">
                                        <a class="dropdown-item status-option" href="#" data-value="delivered" data-id="${order.id}">Entregue</a>
                                        <a class="dropdown-item status-option" href="#" data-value="pending" data-id="${order.id}">Pendente</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    let formattedDate = order.delivered_at ? moment(order.delivered_at).format('DD/MM/YYYY HH:mm') : '-';
                    rowHtml = `
                        <tr class="border-bottom" style="border-bottom-color: #f1f5f9 !important;">
                            <td class="align-middle pl-2 font-weight-bold" style="color: #475569;">#${order.code}</td>
                            <td class="align-middle address-cell">
                                <span class="display-address" style="cursor: pointer; color: #475569;">
                                    <span class="address-text-${order.id}">${currentAddress}</span> <i class="fas fa-pencil-alt fa-xs edit-icon" title="Editar"></i>
                                </span>
                                <input type="text" class="form-control form-control-sm edit-input custom-input" name="orders[${order.id}][address]" data-id="${order.id}" value="${currentAddress}" style="display: none;">
                            </td>
                            <td class="align-middle" style="color: #475569;">${formattedDate}</td>
                        </tr>
                    `;
                }
                $(bodySelector).append(rowHtml);
            });

            self.bindDynamicInteractions();
        });
    },

    // 8. Vincula eventos de edição nos elementos renderizados pelo AJAX
    bindDynamicInteractions: function() {
        const self = this;

        $('.display-address').off('click').on('click', function() {
            $(this).hide();
            $(this).siblings('.edit-input').show().focus();
        });

        $('.edit-input').off('change blur').on('change blur', function() {
            let id = $(this).data('id');
            let val = $(this).val();
            
            if (!self.state.editedOrders[id]) self.state.editedOrders[id] = {};
            self.state.editedOrders[id].address = val; 
            
            $('.address-text-' + id).text(val);
        });

        $('.edit-input').off('keyup').on('keyup', function(e) {
            if (e.key === "Escape" || e.key === "Enter") {
                 $(this).blur();
                 $(this).hide();
                 $(this).siblings('.display-address').show();
            }
        });

        $('.status-option').off('click').on('click', function(e) {
            e.preventDefault();
            let text = $(this).text();
            let val = $(this).data('value');
            let id = $(this).data('id');
            
            if (!self.state.editedOrders[id]) self.state.editedOrders[id] = {};
            self.state.editedOrders[id].status = val; 
            
            $('.status-label-' + id).text(text);
        });
    },

    // 9. Processa o Submit dos Modais (PUT)
    bindSaveForm: function() {
        const self = this;

        $('#modalOrdersForm, #modalDeliveredForm').submit(function(e) {
            e.preventDefault();
            
            let currentModal = $(this).closest('.modal');
            let formUrl = $(this).attr('action'); // Lê a URL gerada pelo Blade no form

            // Aborta se nada foi editado
            if (Object.keys(self.state.editedOrders).length === 0) {
                currentModal.modal('hide');
                return;
            }

            let btnSubmit = $(this).find('button[type="submit"]');
            let originalText = btnSubmit.html();
            btnSubmit.html('<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...').prop('disabled', true);

            $.ajax({
                url: formUrl,
                type: 'PUT',
                data: {
                    _token: $('input[name="_token"]').val(),
                    orders: self.state.editedOrders
                },
                success: function(response) {
                    currentModal.modal('hide');
                    btnSubmit.html(originalText).prop('disabled', false);
                    window.location.reload(); 
                },
                error: function() {
                    alert('Ocorreu um erro ao salvar as alterações.');
                    btnSubmit.html(originalText).prop('disabled', false);
                }
            });
        });
    }
};

// Start 
$(document).ready(function() {
    DriverPerformancePanel.init();
});