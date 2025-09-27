// Função para abrir WhatsApp
function abrirWhatsapp(numero, titulo) {
    const mensagem = encodeURIComponent(`Oi! Vi seu pedido no AjudaJá: "${titulo}". Como posso ajudar?`);
    window.open(`https://wa.me/55${numero}?text=${mensagem}`, '_blank');
}

// Função para marcar como concluído (via AJAX)
// O parâmetro 'isDashboard' ajuda a saber qual elemento remover da tela
function marcarConcluido(id, isDashboard = false) {
    if (!confirm('Tem certeza que deseja marcar este pedido como concluído?')) return;

    fetch('../includes/atualizar_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&status=Concluído`
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('atualizado')) {
            alert('Pedido marcado como concluído!');
            // Remove o card da tela sem recarregar a página
            const prefix = isDashboard ? 'pedido-dash-' : 'pedido-';
            const elementoPedido = document.getElementById(prefix + id);
            if (elementoPedido) {
                elementoPedido.style.transition = 'opacity 0.5s';
                elementoPedido.style.opacity = '0';
                setTimeout(() => elementoPedido.remove(), 500);
            }
        } else {
            alert('Erro ao atualizar o status: ' + data);
        }
    })
    .catch(error => {
        console.error('Erro de conexão:', error);
        alert('Erro de conexão ao tentar atualizar.');
    });
}


// Inicializa os scripts quando o conteúdo da página for carregado
document.addEventListener('DOMContentLoaded', function() {
    
    // Gráficos na Dashboard (agora com dados reais vindos do PHP)
    const graficoCatEl = document.getElementById('grafico-categoria');
    if (graficoCatEl && typeof dadosCategoria !== 'undefined') {
        const ctxCat = graficoCatEl.getContext('2d');
        new Chart(ctxCat, {
            type: 'pie',
            data: {
                labels: dadosCategoria.labels,
                datasets: [{
                    data: dadosCategoria.data,
                    backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#9E9E9E', '#f44336', '#673AB7'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const graficoUrgEl = document.getElementById('grafico-urgencia');
    if (graficoUrgEl && typeof dadosUrgencia !== 'undefined') {
        const ctxUrg = graficoUrgEl.getContext('2d');
        new Chart(ctxUrg, {
            type: 'doughnut',
            data: {
                labels: dadosUrgencia.labels,
                datasets: [{
                    data: dadosUrgencia.data,
                    backgroundColor: ['#f44336', '#ff9800', '#9e9e9e'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }
});