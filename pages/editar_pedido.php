<?php
include_once '../includes/session.php'; // Alterado de session_start()
include '../includes/config.php';
include '../includes/autenticacao.php'; // Protege a página

// Gera o token CSRF para o formulário
gerar_token_csrf();

// Pega o ID do pedido da URL e valida
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$erro = null;
$pedido = null;

if (!$pedido_id) {
    $_SESSION['erro'] = "ID de pedido inválido.";
    header('Location: dashboard.php');
    exit;
}

$sql = "SELECT * FROM pedidos WHERE id = $1 AND usuario_id = $2";

if (!@pg_prepare($conn, "get_pedido_for_edit", $sql)) {
    $_SESSION['erro'] = "Erro ao preparar a consulta: " . pg_last_error($conn);
    header('Location: dashboard.php');
    exit;
}

$result = pg_execute($conn, "get_pedido_for_edit", array($pedido_id, $_SESSION['usuario_id']));

if (!$result || pg_num_rows($result) !== 1) {
    $_SESSION['erro'] = "Você não tem permissão para editar este pedido ou o pedido não existe.";
    header('Location: dashboard.php');
    exit;
}

$pedido = pg_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido - AjudaJá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                 <a class="navbar-brand" href="dashboard.php"><i data-lucide="arrow-left"></i> Voltar</a>
                 <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="../includes/logout.php">Sair</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-4">
        <h2 class="mb-4"><i data-lucide="edit"></i> Editar Pedido de Ajuda</h2>

        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
        <?php endif; ?>

        <form action="../includes/processa_edicao.php" method="POST" id="form-edicao" class="row g-3">
            
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">

            <div class="col-md-12">
                <label for="titulo" class="form-label">Título do Pedido</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="255" value="<?php echo htmlspecialchars($pedido['titulo']); ?>">
            </div>
            <div class="col-md-12">
                <label for="descricao" class="form-label">Descrição Detalhada</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="5" required><?php echo htmlspecialchars($pedido['descricao']); ?></textarea>
            </div>
            <div class="col-md-6">
                <label for="urgencia" class="form-label">Nível de Urgência</label>
                <select class="form-select" id="urgencia" name="urgencia" required>
                    <option value="Urgente" <?php echo ($pedido['urgencia'] == 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                    <option value="Pode Esperar" <?php echo ($pedido['urgencia'] == 'Pode Esperar') ? 'selected' : ''; ?>>Pode Esperar</option>
                    <option value="Daqui a uma Semana" <?php echo ($pedido['urgencia'] == 'Daqui a uma Semana') ? 'selected' : ''; ?>>Daqui a uma Semana</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="categoria" class="form-label">Categoria</label>
                <select class="form-select" id="categoria" name="categoria" required>
                    <option value="Cesta Básica" <?php echo ($pedido['categoria'] == 'Cesta Básica') ? 'selected' : ''; ?>>Cesta Básica</option>
                    <option value="Carona" <?php echo ($pedido['categoria'] == 'Carona') ? 'selected' : ''; ?>>Carona</option>
                    <option value="Apoio Emocional" <?php echo ($pedido['categoria'] == 'Apoio Emocional') ? 'selected' : ''; ?>>Apoio Emocional</option>
                    <option value="Doação de Itens" <?php echo ($pedido['categoria'] == 'Doação de Itens') ? 'selected' : ''; ?>>Doação de Itens</option>
                    <option value="Serviços Voluntários" <?php echo ($pedido['categoria'] == 'Serviços Voluntários') ? 'selected' : ''; ?>>Serviços Voluntários</option>
                    <option value="Outros" <?php echo ($pedido['categoria'] == 'Outros') ? 'selected' : ''; ?>>Outros</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success w-100">
                    <i data-lucide="save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </main>
    <?php if ($conn) { pg_close($conn); } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script> lucide.createIcons(); </script>
</body>
</html>