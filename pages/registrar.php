<?php 
// 1. Incluir config PRIMEIRO para BASE_URL e conexão com banco (se necessário)
include_once '../includes/config.php'; 
// 2. Incluir session DEPOIS para usar a sessão
require_once '../includes/session.php'; 

// Recuperar erros e dados antigos
$erros = $_SESSION['erro_campos'] ?? [];
$old_data = $_SESSION['old_data'] ?? [];
unset($_SESSION['erro_campos'], $_SESSION['old_data']);

// Funções auxiliares (ok)
function exibir_erro($campo, $erros) { /* ... */ }
function old($campo, $old_data) { /* ... */ }

// Define o título da página ANTES de incluir o header
$page_title = 'Registrar - AjudaJá'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body>
    <?php include '../includes/header.php'; ?> 

    <main class="container my-5" style="max-width: 500px;">
        <h2 class="mb-4 text-center">Crie sua Conta</h2>
        <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>" . $_SESSION['erro'] . "</div>"; unset($_SESSION['erro']); } ?>
        
        <form action="../includes/processa_registro.php" method="POST">
             <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="mb-3">
                <label for="nome" class="form-label">Nome Completo</label>
                <input type="text" class="form-control <?php echo isset($erros['nome']) ? 'is-invalid' : ''; ?>" id="nome" name="nome" value="<?php echo old('nome', $old_data); ?>" required>
                <?php exibir_erro('nome', $erros); ?>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control <?php echo isset($erros['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo old('email', $old_data); ?>" required>
                <?php exibir_erro('email', $erros); ?>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone (com DDD)</label>
                <input type="tel" class="form-control <?php echo isset($erros['telefone']) ? 'is-invalid' : ''; ?>" id="telefone" name="telefone" value="<?php echo old('telefone', $old_data); ?>" placeholder="Ex: 11988887777" required>
                <?php exibir_erro('telefone', $erros); ?>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control <?php echo isset($erros['senha']) ? 'is-invalid' : ''; ?>" id="senha" name="senha" required aria-describedby="senhaHelp">
                <div id="senhaHelp" class="form-text">Mínimo 8 caracteres, com pelo menos uma letra e um número.</div>
                <?php exibir_erro('senha', $erros); ?>
            </div>
            <div class="mb-3">
                <label for="senha_confirm" class="form-label">Confirme sua Senha</label>
                <input type="password" class="form-control <?php echo isset($erros['senha_confirm']) ? 'is-invalid' : ''; ?>" id="senha_confirm" name="senha_confirm" required>
                <?php exibir_erro('senha_confirm', $erros); ?>
            </div>
            <button type="submit" class="btn btn-success w-100">Registrar</button>
            <div class="text-center mt-3">
                <p>Já tem uma conta? <a href="<?php echo BASE_URL; ?>/pages/login.php">Faça login</a></p> 
            </div>
        </form>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>