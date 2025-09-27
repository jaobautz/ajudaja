<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AjudaJá</title>
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
        <h2 class="mb-4 text-center">Acesse sua Conta</h2>
        <?php if (isset($_SESSION['sucesso'])) { echo "<div class='alert alert-success'>" . $_SESSION['sucesso'] . "</div>"; unset($_SESSION['sucesso']); } ?>
        <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>" . $_SESSION['erro'] . "</div>"; unset($_SESSION['erro']); } ?>
        <form action="../includes/processa_login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Entrar</button>
            <div class="text-center mt-3">
                <p>Não tem uma conta? <a href="registrar.php">Crie uma agora</a></p>
            </div>
        </form>
    </main>
</body>
</html>