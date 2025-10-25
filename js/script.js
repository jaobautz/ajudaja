// =======================================================
// AJUDAJÁ - SCRIPTS GLOBAIS V2.1 (Com Máscaras)
// =======================================================

/**
 * Abre o WhatsApp com uma mensagem pré-formatada.
 * @param {string} numero Número de telefone (apenas dígitos).
 * @param {string} titulo Título do pedido.
 */
function abrirWhatsapp(numero, titulo) {
    const numeroLimpo = numero.replace(/\D/g, '');
    const mensagem = encodeURIComponent(`Oi! Vi seu pedido no AjudaJá: "${titulo}". Como posso ajudar?`);
    window.open(`https://wa.me/55${numeroLimpo}?text=${mensagem}`, '_blank');
}

/**
 * Marca um pedido como concluído via AJAX.
 * @param {number} id ID do pedido.
 */
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
            const elementoPedido = document.getElementById('pedido-dash-' + id);
            if (elementoPedido) {
                elementoPedido.classList.add('opacity-50');
                const badge = elementoPedido.querySelector('.badge');
                if (badge) { badge.classList.remove('bg-success'); badge.classList.add('bg-secondary'); badge.textContent = 'Concluído'; }
                if(botao) botao.remove();
            }
        } else {
            alert('Erro ao atualizar o status: ' + data);
             if (botao) { botao.disabled = false; botao.innerHTML = '<i data-lucide="check-circle"></i> Concluir'; if(lucide) lucide.createIcons(); }
        }
    })
    .catch(error => {
        console.error('Erro de conexão:', error);
        alert('Erro de conexão ao tentar atualizar.');
         if (botao) { botao.disabled = false; botao.innerHTML = '<i data-lucide="check-circle"></i> Concluir'; if(lucide) lucide.createIcons(); }
    });
}


// Inicializa os scripts quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {

    // --- Inicialização de Ícones Lucide ---
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // --- Gráficos na Dashboard ---
    const graficoCatEl = document.getElementById('grafico-categoria');
    if (graficoCatEl && typeof Chart !== 'undefined' && typeof dadosCategoria !== 'undefined' && dadosCategoria.data.length > 0) {
        const ctxCat = graficoCatEl.getContext('2d');
        new Chart(ctxCat, { type: 'pie', data: { labels: dadosCategoria.labels, datasets: [{ data: dadosCategoria.data, backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#6B7280', '#EF4444', '#6366F1'], hoverOffset: 4 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
    }
    const graficoUrgEl = document.getElementById('grafico-urgencia');
    if (graficoUrgEl && typeof Chart !== 'undefined' && typeof dadosUrgencia !== 'undefined' && dadosUrgencia.data.length > 0) {
        const ctxUrg = graficoUrgEl.getContext('2d');
        new Chart(ctxUrg, { type: 'doughnut', data: { labels: dadosUrgencia.labels, datasets: [{ data: dadosUrgencia.data, backgroundColor: ['#EF4444', '#F59E0B', '#6B7280'], hoverOffset: 4 }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });
    }

    // --- Interação Formulário de Resposta Comentários ---
    document.querySelectorAll('.resposta-form').forEach(form => form.style.display = 'none');
    document.querySelectorAll('.btn-responder').forEach(button => { button.addEventListener('click', function() { const commentId = this.getAttribute('data-comment-id'); const form = document.getElementById('form-resposta-' + commentId); if (form) { document.querySelectorAll('.resposta-form').forEach(f => f.style.display = 'none'); form.style.display = 'block'; form.querySelector('textarea').focus(); } }); });
    document.querySelectorAll('.btn-cancelar-resposta').forEach(button => { button.addEventListener('click', function() { const commentId = this.getAttribute('data-comment-id'); const form = document.getElementById('form-resposta-' + commentId); if (form) { form.style.display = 'none'; } }); });

    // --- Rolagem Automática para Fim do Chat ---
    const chatBody = document.getElementById('chat-body');
    if(chatBody) { chatBody.scrollTop = chatBody.scrollHeight; }

    // =============================================
    // MÁSCARAS DE INPUT (Usando Inputmask.js via CDN)
    // =============================================
    if (typeof Inputmask !== 'undefined') { // Verifica se a biblioteca carregou
        // Máscara CEP (encontrada em cadastrar.php e editar_pedido.php)
        const cepInput = document.getElementById('cep');
        if (cepInput) {
            Inputmask('99999-999', { clearIncomplete: true }).mask(cepInput);
             // Opcional: Adicionar AJAX para ViaCEP aqui para preenchimento automático
        }

        // Máscara Telefone (encontrada em registrar.php e perfil.php -> id='perfil_telefone')
        const telInput = document.getElementById('telefone');
        if (telInput) {
            Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true, clearIncomplete: true }).mask(telInput);
        }
        const perfilTelInput = document.getElementById('perfil_telefone'); // Campo telefone no perfil
         if (perfilTelInput) {
            Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true, clearIncomplete: true }).mask(perfilTelInput);
        }


        // Máscara WhatsApp (encontrada em cadastrar.php)
        const whatsappInput = document.getElementById('whatsapp');
        if (whatsappInput) {
             Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true, clearIncomplete: true }).mask(whatsappInput);
        }
    } else {
        console.warn("Biblioteca Inputmask não carregada. Máscaras não serão aplicadas.");
    }
    // =============================================
    // FIM: Máscaras de Input
    // =============================================

}); // Fim DOMContentLoaded