// =======================================================
// AJUDAJÁ - SCRIPTS GLOBAIS V2.1 (Revertido)
// =======================================================

// ... (Funções abrirWhatsapp e marcarConcluido - Sem alterações) ...
function abrirWhatsapp(numero, titulo) { /* ... */ }
function marcarConcluido(id) { /* ... */ }

// Inicializa os scripts quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {

    // --- Inicialização de Ícones Lucide ---
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // --- Gráficos na Dashboard ---
    const graficoCatEl = document.getElementById('grafico-categoria'); if (graficoCatEl && typeof Chart !== 'undefined' && typeof dadosCategoria !== 'undefined' && dadosCategoria.data.length > 0) { /* ... */ }
    const graficoUrgEl = document.getElementById('grafico-urgencia'); if (graficoUrgEl && typeof Chart !== 'undefined' && typeof dadosUrgencia !== 'undefined' && dadosUrgencia.data.length > 0) { /* ... */ }

    // --- Interação Formulário de Resposta Comentários ---
    document.querySelectorAll('.resposta-form').forEach(form => form.style.display = 'none'); document.querySelectorAll('.btn-responder').forEach(button => { /* ... */ }); document.querySelectorAll('.btn-cancelar-resposta').forEach(button => { /* ... */ });

    // --- Rolagem Automática para Fim do Chat ---
    const chatBody = document.getElementById('chat-body');
    if(chatBody) { chatBody.scrollTop = chatBody.scrollHeight; }

    // =============================================
    // MÁSCARAS DE INPUT (ATIVAS)
    // =============================================
    if (typeof Inputmask !== 'undefined') { 
        
        // Máscara CEP (encontrada em cadastrar.php, editar_pedido.php, index.php)
        // O seletor agora inclui #cep e #filtro_cep
        Inputmask('99999-999', { clearIncomplete: true }).mask(document.querySelectorAll('#cep, #filtro_cep'));
        
        // Máscara Telefone (encontrada em registrar.php e perfil.php)
        Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true, clearIncomplete: true }).mask(document.querySelectorAll('#telefone, #perfil_telefone'));
        
        // Máscara WhatsApp (encontrada em cadastrar.php)
        const whatsappInput = document.getElementById('whatsapp');
        if (whatsappInput) {
             Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true, clearIncomplete: true }).mask(whatsappInput);
        }
        
        // O CÓDIGO DE AUTO-PREENCHIMENTO AJAX FOI REMOVIDO DESTA VERSÃO

    } else {
        console.warn("Biblioteca Inputmask não carregada. Máscaras não serão aplicadas.");
    }
    // =============================================
    // FIM: Máscaras de Input
    // =============================================

}); // Fim DOMContentLoaded