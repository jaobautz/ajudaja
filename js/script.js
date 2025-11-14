// =======================================================
// AJUDAJÁ - SCRIPTS GLOBAIS V2.3 (Revisão Final Local)
// =======================================================

/**
 * Funções de Ação (marcarConcluido, etc.)
 */
function abrirWhatsapp(numero, titulo) {
    const numeroLimpo = numero.replace(/\D/g, ''); 
    const mensagem = encodeURIComponent(`Oi! Vi seu pedido no AjudaJá: "${titulo}". Como posso ajudar?`);
    window.open(`https://wa.me/55${numeroLimpo}?text=${mensagem}`, '_blank');
}

function marcarConcluido(id) {
    if (!confirm('Tem certeza que deseja marcar este pedido como concluído?')) return;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const botao = document.querySelector(`#pedido-dash-${id} .btn-success[onclick*='marcarConcluido']`); 
    const spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'; 
    if (botao) { botao.disabled = true; botao.innerHTML = spinner + ' Concluindo...'; }

    fetch('../includes/atualizar_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
        body: `id=${id}&status=Concluído&csrf_token=${token}`
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('atualizado')) {
            const elementoPedido = document.getElementById('pedido-dash-'.concat(id)); 
            if (elementoPedido) {
                elementoPedido.classList.add('opacity-50');
                const badge = elementoPedido.querySelector('.badge');
                if (badge) { badge.classList.remove('bg-success'); badge.classList.add('bg-secondary'); badge.textContent = 'Concluído'; }
                if(botao) botao.remove();
            }
        } else {
            alert('Erro ao atualizar o status: ' + data);
             if (botao) { botao.disabled = false; botao.innerHTML = '<i data-lucide="check-circle"></i> Concluir'; if(typeof lucide !== 'undefined') lucide.createIcons(); }
        }
    })
    .catch(error => {
        console.error('Erro de conexão:', error);
        alert('Erro de conexão ao tentar atualizar.');
         if (botao) { botao.disabled = false; botao.innerHTML = '<i data-lucide="check-circle"></i> Concluir'; if(typeof lucide !== 'undefined') lucide.createIcons(); }
    });
}

// =======================================================
// INICIALIZAÇÃO PRINCIPAL
// =======================================================
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. Inicialização de Ícones Lucide ---
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    } else {
        console.warn("Biblioteca Lucide Icons não carregada.");
    }

    // --- 2. Gráficos na Dashboard ---
    const graficoCatEl = document.getElementById('grafico-categoria');
    if (graficoCatEl) { // Se o <canvas> existe...
        if (typeof Chart !== 'undefined' && typeof dadosCategoria !== 'undefined' && dadosCategoria.data.length > 0) {
            const ctxCat = graficoCatEl.getContext('2d');
            new Chart(ctxCat, { type: 'pie', data: { labels: dadosCategoria.labels, datasets: [{ data: dadosCategoria.data, backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#6B7280', '#EF4444', '#6366F1'], hoverOffset: 4 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
        } else if (typeof Chart === 'undefined') {
            console.error("Chart.js não carregou. Gráfico de Categoria não pode ser renderizado.");
        }
    }
    
    const graficoUrgEl = document.getElementById('grafico-urgencia');
    if (graficoUrgEl) { // Se o <canvas> existe...
        if (typeof Chart !== 'undefined' && typeof dadosUrgencia !== 'undefined' && dadosUrgencia.data.length > 0) {
            const ctxUrg = graficoUrgEl.getContext('2d');
            new Chart(ctxUrg, { type: 'doughnut', data: { labels: dadosUrgencia.labels, datasets: [{ data: dadosUrgencia.data, backgroundColor: ['#EF4444', '#F59E0B', '#6B7280'], hoverOffset: 4 }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });
        } else if (typeof Chart === 'undefined') {
            console.error("Chart.js não carregou. Gráfico de Urgência não pode ser renderizado.");
        }
    }

    // --- 3. Interação Formulário de Resposta Comentários ---
    document.querySelectorAll('.resposta-form').forEach(form => form.style.display = 'none');
    document.querySelectorAll('.btn-responder').forEach(button => { button.addEventListener('click', function() { const commentId = this.getAttribute('data-comment-id'); const form = document.getElementById('form-resposta-' + commentId); if (form) { document.querySelectorAll('.resposta-form').forEach(f => f.style.display = 'none'); form.style.display = 'block'; form.querySelector('textarea').focus(); } }); });
    document.querySelectorAll('.btn-cancelar-resposta').forEach(button => { button.addEventListener('click', function() { const commentId = this.getAttribute('data-comment-id'); const form = document.getElementById('form-resposta-' + commentId); if (form) { form.style.display = 'none'; } }); });

    // --- 4. Rolagem Automática para Fim do Chat ---
    const chatBody = document.getElementById('chat-body');
    if(chatBody) { chatBody.scrollTop = chatBody.scrollHeight; }

    // --- 5. Máscaras de Input (ATIVAS) ---
    if (typeof Inputmask !== 'undefined') { // Verifica se a biblioteca carregou
        Inputmask('99999-999', { clearIncomplete: true }).mask(document.querySelectorAll('#cep, #filtro_cep'));
        Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true, clearIncomplete: true }).mask(document.querySelectorAll('#telefone, #perfil_telefone, #whatsapp'));
    } else {
        console.warn("Biblioteca Inputmask não carregada. Máscaras não serão aplicadas.");
    }

    // --- 6. Auto-preenchimento de CEP (AJAX) ---
    const cepField = document.getElementById('cep'); 
    const cidadeField = document.getElementById('cidade');
    const estadoField = document.getElementById('estado');

    if (cepField && cidadeField && estadoField) {
        cepField.addEventListener('blur', function() {
            const cepValue = this.value.replace(/\D/g, ''); 
            if (cepValue.length === 8) { 
                cidadeField.value = 'Buscando...';
                estadoField.value = '...';
                cepField.disabled = true; 
                fetch(`https://viacep.com.br/ws/${cepValue}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.erro) {
                            cidadeField.value = '';
                            estadoField.value = '';
                            cepField.classList.add('is-invalid');
                        } else {
                            cidadeField.value = data.localidade;
                            estadoField.value = data.uf;
                            cepField.classList.remove('is-invalid');
                            cepField.classList.add('is-valid'); 
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar CEP:', error);
                        cidadeField.value = ''; estadoField.value = '';
                        cepField.classList.add('is-invalid');
                    })
                    .finally(() => {
                        cepField.disabled = false;
                    });
            } else if (cepValue.length > 0) {
                 cidadeField.value = ''; estadoField.value = '';
                 cepField.classList.add('is-invalid');
            } else {
                 cidadeField.value = ''; estadoField.value = '';
                 cepField.classList.remove('is-invalid'); cepField.classList.remove('is-valid');
            }
        });
    }

}); // Fim DOMContentLoaded