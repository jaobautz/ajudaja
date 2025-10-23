<?php
// =======================================================
// ARQUIVO DE AJUDA PARA VALIDAÇÃO DE BACKEND
// =======================================================

/**
 * Valida um campo com base em um conjunto de regras.
 *
 * @param string $valor O valor a ser validado.
 * @param array $regras Um array de regras (ex: ['obrigatorio', 'min:3', 'email']).
 * @return string|null Retorna uma mensagem de erro se a validação falhar, ou null se for bem-sucedido.
 */
function validar_campo($valor, $regras) {
    foreach ($regras as $regra) {
        $partes = explode(':', $regra);
        $nome_regra = $partes[0];
        $parametro = $partes[1] ?? null;

        switch ($nome_regra) {
            case 'obrigatorio':
                if (empty(trim($valor))) {
                    return 'Este campo é obrigatório.';
                }
                break;
            
            case 'min':
                if (mb_strlen($valor, 'UTF-8') < $parametro) {
                    return "Este campo deve ter no mínimo {$parametro} caracteres.";
                }
                break;
            
            case 'max':
                if (mb_strlen($valor, 'UTF-8') > $parametro) {
                    return "Este campo deve ter no máximo {$parametro} caracteres.";
                }
                break;
            
            case 'email':
                if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    return 'Por favor, insira um endereço de e-mail válido.';
                }
                break;
            
            case 'numerico':
                if (!ctype_digit($valor)) {
                    return 'Este campo deve conter apenas números.';
                }
                break;
            
            case 'whatsapp':
            case 'telefone': // --- TAREFA 5: Adicionada regra 'telefone' ---
                $numeros = preg_replace('/[^0-9]/', '', $valor);
                if (mb_strlen($numeros) < 10 || mb_strlen($numeros) > 11) {
                    return 'O número deve ter entre 10 e 11 dígitos (com DDD).';
                }
                break;

            case 'in':
                $opcoes_permitidas = explode(',', $parametro);
                if (!in_array($valor, $opcoes_permitidas)) {
                    return 'O valor selecionado é inválido.';
                }
                break;

            case 'confirma': // --- TAREFA 5: Adicionada regra 'confirma' ---
                // Compara o valor com outro campo (ex: 'confirma:senha')
                // O 'parametro' é o *nome* do campo no array $_POST.
                $valor_comparar = trim($_POST[$parametro] ?? '');
                if ($valor !== $valor_comparar) {
                    return "A confirmação não coincide com o campo original.";
                }
                break;
        }
    }
    return null; // Sem erros
}
?>