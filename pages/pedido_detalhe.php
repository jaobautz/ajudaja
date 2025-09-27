<?php
session_start();
include '../includes/config.php';

// Pega o ID do pedido da URL e valida
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$pedido_id) {
    // Se o ID for inválido ou não existir, mostra uma mensagem de erro
    $erro = "Pedido não encontrado.";
} else {
    // Busca os detalhes do pedido e o nome do usuário que o postou
    $sql = "SELECT p.*, u.nome as autor_nome 
            FROM pedidos p
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $pedido = $result->fetch_assoc();
    } else {
        $erro = "Pedido não encontrado ou indisponível.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pedido) ? htmlspecialchars($pedido['titulo']) : 'Detalhes do Pedido'; ?> - AjudaJá</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-dark bg-success">
            <div class="container">
                 <a class="navbar-brand" href="index.php"><i class="fas fa-arrow-left"></i> Voltar para a lista</a>
                 <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="../includes/logout.php">Sair</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-5">
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger text-center"><?php echo $erro; ?></div>
        <?php else: ?>
            <div class="detalhe-container">
                <div class="detalhe-header">
                    <span class="urgencia-tag <?php echo strtolower(str_replace(' ', '-', $pedido['urgencia'])); ?>"><?php echo htmlspecialchars($pedido['urgencia']); ?></span>
                    <h1><?php echo htmlspecialchars($pedido['titulo']); ?></h1>
                    <p class="detalhe-meta">
                        Postado por <strong><?php echo htmlspecialchars($pedido['autor_nome']); ?></strong> 
                        em <?php echo date('d/m/Y \à\s H:i', strtotime($pedido['data_postagem'])); ?>
                    </p>
                </div>
                <hr>
                <div class="detalhe-body">
                    <p><strong>Categoria:</strong> <?php echo htmlspecialchars($pedido['categoria']); ?></p>
                    <p class="detalhe-descricao">
                        <?php echo nl2br(htmlspecialchars($pedido['descricao'])); // nl2br preserva as quebras de linha ?>
                    </p>
                </div>
                <div class="detalhe-footer">
                    <button class="ajudar-btn" onclick="abrirWhatsapp('<?php echo htmlspecialchars($pedido['whatsapp_numero']); ?>', '<?php echo htmlspecialchars(addslashes($pedido['titulo'])); ?>')">
                        <i class="fab fa-whatsapp"></i> Quero Ajudar
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js" defer></script>
</body>
</html>