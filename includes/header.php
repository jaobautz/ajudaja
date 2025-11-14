<?php
// Usamos require_once para garantir que config.php e session.php sejam carregados
// apenas uma vez, na ordem correta.
require_once 'config.php'; // Define BASE_URL primeiro
require_once 'session.php'; // Inicia a sessão e funções CSRF

gerar_token_csrf();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title><?php echo $page_title ?? 'AjudaJá'; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">

    <?php if (isset($include_chartjs) && $include_chartjs): ?>
        <script src="<?php echo BASE_URL; ?>/js/lib/chart.min.js"></script>
    <?php endif; ?>

</head>
<body>
    <header class="header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>/pages/index.php"><i data-lucide="heart-handshake"></i> AjudaJá</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/pages/dashboard.php">Meu Painel</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/pages/perfil.php">Meu Perfil</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/pages/caixa_entrada.php"><i data-lucide="message-square"></i> Conversas</a></li>
                            <li class="nav-item ms-lg-2">
                                <a class="btn btn-sm btn-novo-pedido" href="<?php echo BASE_URL; ?>/pages/cadastrar.php">
                                    <i data-lucide="plus-circle"></i> Novo Pedido
                                </a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/includes/logout.php">Sair</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/pages/login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/pages/registrar.php">Registrar</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>