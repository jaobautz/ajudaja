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
            case 'telefone': 
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

            case 'confirma': 
                $valor_comparar = trim($_POST[$parametro] ?? '');
                if ($valor !== $valor_comparar) {
                    return "A confirmação não coincide com o campo original.";
                }
                break;
            
            case 'cep': // Regra adicionada para Geolocalização
                $cep_limpo = preg_replace('/[^0-9]/', '', $valor);
                if (!empty($cep_limpo) && (strlen($cep_limpo) !== 8 || !ctype_digit($cep_limpo))) {
                     return 'O CEP deve estar no formato 00000-000 ou 00000000.';
                }
                break;

            case 'senha_forte':
                if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $valor)) {
                    return 'A senha deve conter pelo menos uma letra e um número.';
                }
                break;
        }
    }
    return null; // Sem erros
}
?>