<?php 
include_once '../includes/session.php'; 

// TAREFA 4: Recuperar erros de validação e dados antigos
$erros = $_SESSION['erro_campos'] ?? [];
$old_data = $_SESSION['old_data'] ?? [];
unset($_SESSION['erro_campos'], $_SESSION['old_data']);

// Função auxiliar para exibir o erro
function exibir_erro($campo, $erros) {
    if (isset($erros[$campo])) {
        echo "<div class='invalid-feedback d-block'>{$erros[$campo]}</div>";
    }
}
// Função auxiliar para manter o valor antigo
function old($campo, $old_data) {
    return htmlspecialchars($old_data[$campo] ?? '');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - AjudaJá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar navbar-dark bg-success">
            <div class="container">
                <a class="navbar-brand" href="index.php"><i class="fas fa-hands-helping"></i> AjudaJá</a>
            </div>
        </nav>
    </header>

    <main class="container my-5" style="max-width: 500px;">
        <h2 class="mb-4 text-center">Crie sua Conta</h2>
        <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>" . $_SESSION['erro'] . "</div>"; unset($_SESSION['erro']); } ?>
        
        <form action="../includes/processa_registro.php" method="POST">
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
                <label for="senha" class="form-label">Senha (mínimo 8 caracteres)</label>
                <input type="password" class="form-control <?php echo isset($erros['senha']) ? 'is-invalid' : ''; ?>" id="senha" name="senha" required>
                <?php exibir_erro('senha', $erros); ?>
            </div>
            <button type="submit" class="btn btn-success w-100">Registrar</button>
            <div class="text-center mt-3">
                <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
            </div>
        </form>
    </main>
</body>
</html>