const DashboardPanel = {
    init: function() {
        this.initPieChart();
    },

    initPieChart: function() {
        const canvas = document.getElementById("myPieChart");
        
        // Proteção: Se o canvas não existir na página, não roda o script
        if (!canvas) return;

        // Resgata os dados dinâmicos do Laravel passados pelo HTML (data- attributes)
        const deliveredOrders = parseInt(canvas.dataset.delivered) || 0;
        const pendingOrders = parseInt(canvas.dataset.pending) || 0;

        const ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ["Entregues", "Pendentes"],
                datasets: [{
                    data: [deliveredOrders, pendingOrders],
                    backgroundColor: ['#198754', '#ffc107'],
                    hoverBackgroundColor: ['#157347', '#ffca2c'],
                    borderWidth: 2,
                }],
            },
            options: {
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            font: { family: 'Nunito', weight: 'bold' }
                        }
                    }
                }
            },
        });
    }
};

// Inicializa quando o DOM estiver completamente carregado
document.addEventListener("DOMContentLoaded", function() {
    DashboardPanel.init();
});