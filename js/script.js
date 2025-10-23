// Função para abrir WhatsApp
function abrirWhatsapp(numero, titulo) {
    const mensagem = encodeURIComponent(`Oi! Vi seu pedido no AjudaJá: "${titulo}". Como posso ajudar?`);
    window.open(`https://wa.me/55${numero}?text=${mensagem}`, '_blank');
}

// Função para marcar como concluído (via AJAX)
function marcarConcluido(id) {
    if (!confirm('Tem certeza que deseja marcar este pedido como concluído?')) return;

    // TAREFA 2: Obter o token CSRF da tag meta no <head>
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('../includes/atualizar_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        // TAREFA 2: Enviar o ID, o status E o token CSRF
        body: `id=${id}&status=Concluído&csrf_token=${token}`
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('atualizado')) {
            alert('Pedido marcado como concluído!');
            // Atualiza a interface sem recarregar
            const elementoPedido = document.getElementById('pedido-dash-' + id);
            if (elementoPedido) {
                elementoPedido.classList.add('opacity-50');
                const badge = elementoPedido.querySelector('.badge');
                if (badge) {
                    badge.classList.remove('bg-success');
                    badge.classList.add('bg-secondary');
                    badge.textContent = 'Concluído';
                }
                const botaoConcluir = elementoPedido.querySelector('.btn-success');
                if (botaoConcluir) {
                    botaoConcluir.remove();
                }
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
    
    // Gráficos na Dashboard
    const graficoCatEl = document.getElementById('grafico-categoria');
    if (graficoCatEl && typeof dadosCategoria !== 'undefined' && dadosCategoria.data.length > 0) {
        const ctxCat = graficoCatEl.getContext('2d');
        new Chart(ctxCat, {
            type: 'pie',
            data: {
                labels: dadosCategoria.labels,
                datasets: [{
                    data: dadosCategoria.data,
                    backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#6B7280', '#EF4444', '#6366F1'],
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
    if (graficoUrgEl && typeof dadosUrgencia !== 'undefined' && dadosUrgencia.data.length > 0) {
        const ctxUrg = graficoUrgEl.getContext('2d');
        new Chart(ctxUrg, {
            type: 'doughnut',
            data: {
                labels: dadosUrgencia.labels,
                datasets: [{
                    data: dadosUrgencia.data,
                    backgroundColor: ['#EF4444', '#F59E0B', '#6B7280'],
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