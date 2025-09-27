<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AjudaJá - Home</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-dark bg-success">
            <div class="container">
                <a class="navbar-brand" href="index.php"><i class="fas fa-hands-helping"></i> AjudaJá</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="cadastrar.php"><i class="fas fa-plus"></i> Novo Pedido</a></li>
                            <li class="nav-item"><a class="nav-link" href="../includes/logout.php">Sair</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="registrar.php">Registrar</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-4">
        <h2 class="mb-4"><i class="fas fa-list"></i> Pedidos de Ajuda Recentes</h2>
        
        <form action="index.php" method="GET" class="row mb-3">
            <div class="col-md-6 mb-2">
                <input type="text" class="form-control" name="busca" placeholder="Buscar por título ou categoria..." value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
            </div>
            <div class="col-md-3 mb-2">
                <select class="form-select" name="urgencia">
                    <option value="">Todas Urgências</option>
                    <option value="Urgente" <?php echo (isset($_GET['urgencia']) && $_GET['urgencia'] == 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                    <option value="Pode Esperar" <?php echo (isset($_GET['urgencia']) && $_GET['urgencia'] == 'Pode Esperar') ? 'selected' : ''; ?>>Pode Esperar</option>
                    <option value="Daqui a uma Semana" <?php echo (isset($_GET['urgencia']) && $_GET['urgencia'] == 'Daqui a uma Semana') ? 'selected' : ''; ?>>Daqui a uma Semana</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-outline-success w-100">Filtrar</button>
            </div>
        </form>
        
        <div id="lista-pedidos" class="row">
            <?php include '../includes/lista_pedidos.php'; ?>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js" defer></script>
</body>
</html>