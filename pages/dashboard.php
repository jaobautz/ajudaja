<?php
session_start();
include '../includes/config.php';

// Protege a página: se o usuário não estiver logado, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Contagens para Stats (APENAS DO USUÁRIO LOGADO)
$stmt_total = $conn->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id = ?");
$stmt_total->bind_param("i", $usuario_id);
$stmt_total->execute();
$total = $stmt_total->get_result()->fetch_row()[0];
$stmt_total->close();

$stmt_urgentes = $conn->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id = ? AND urgencia='Urgente'");
$stmt_urgentes->bind_param("i", $usuario_id);
$stmt_urgentes->execute();
$urgentes = $stmt_urgentes->get_result()->fetch_row()[0];
$stmt_urgentes->close();

$stmt_abertos = $conn->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id = ? AND status='Aberto'");
$stmt_abertos->bind_param("i", $usuario_id);
$stmt_abertos->execute();
$abertos = $stmt_abertos->get_result()->fetch_row()[0];
$stmt_abertos->close();

$concluidos = $total - $abertos;

// Contagens por Categoria para o gráfico (APENAS DO USUÁRIO LOGADO)
$stmt_cat = $conn->prepare("SELECT categoria, COUNT(*) as count FROM pedidos WHERE usuario_id = ? GROUP BY categoria ORDER BY count DESC");
$stmt_cat->bind_param("i", $usuario_id);
$stmt_cat->execute();
$categorias_result = $stmt_cat->get_result();
$labels_cat = [];
$data_cat = [];
while ($row = $categorias_result->fetch_assoc()) {
    $labels_cat[] = $row['categoria'];
    $data_cat[] = $row['count'];
}
$stmt_cat->close();

// Contagens por Urgência para o gráfico (APENAS DO USUÁRIO LOGADO)
$stmt_urg = $conn->prepare("SELECT urgencia, COUNT(*) as count FROM pedidos WHERE usuario_id = ? GROUP BY urgencia");
$stmt_urg->bind_param("i", $usuario_id);
$stmt_urg->execute();
$urgencias_result = $stmt_urg->get_result();
$labels_urg = [];
$data_urg = [];
while ($row = $urgencias_result->fetch_assoc()) {
    $labels_urg[] = $row['urgencia'];
    $data_urg[] = $row['count'];
}
$stmt_urg->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AjudaJá</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar navbar-dark bg-success">
            <div class="container">
                <a class="navbar-brand" href="index.php"><i class="fas fa-arrow-left"></i> Voltar à Home</a>
                <a class="navbar-brand" href="cadastrar.php"><i class="fas fa-plus"></i> Novo Pedido</a>
            </div>
        </nav>
    </header>

    <main class="container my-4">
        <h2 class="mb-4"><i class="fas fa-chart-bar"></i> Meu Dashboard</h2>
        <h5 class="text-muted mb-4">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h5>
        
        <div class="row mb-4">
            <div class="col-md-3 mb-3"><div class="card text-center bg-light"><div class="card-body"><i class="fas fa-list fa-2x text-success"></i><h5 class="card-title">Total de Pedidos</h5><p class="fs-3"><?php echo $total; ?></p></div></div></div>
            <div class="col-md-3 mb-3"><div class="card text-center bg-light"><div class="card-body"><i class="fas fa-exclamation-triangle fa-2x text-danger"></i><h5 class="card-title">Urgentes</h5><p class="fs-3"><?php echo $urgentes; ?></p></div></div></div>
            <div class="col-md-3 mb-3"><div class="card text-center bg-light"><div class="card-body"><i class="fas fa-door-open fa-2x text-primary"></i><h5 class="card-title">Abertos</h5><p class="fs-3"><?php echo $abertos; ?></p></div></div></div>
            <div class="col-md-3 mb-3"><div class="card text-center bg-light"><div class="card-body"><i class="fas fa-check-circle fa-2x text-success"></i><h5 class="card-title">Concluídos</h5><p class="fs-3"><?php echo $concluidos; ?></p></div></div></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6"><h5>Meus Pedidos por Categoria</h5><canvas id="grafico-categoria"></canvas></div>
            <div class="col-md-6"><h5>Meus Pedidos por Urgência</h5><canvas id="grafico-urgencia"></canvas></div>
        </div>

        <h3 class="mb-3">Gerenciar Meus Pedidos</h3>
        <div id="lista-pedidos-dash" class="row">
            <?php
            // MODIFICAÇÃO: A query agora também busca o nome do autor (embora seja sempre o mesmo no dashboard, é bom para consistência)
            $stmt_pedidos = $conn->prepare("SELECT p.*, u.nome as autor_nome 
                                            FROM pedidos p
                                            JOIN usuarios u ON p.usuario_id = u.id
                                            WHERE p.usuario_id = ? ORDER BY p.data_postagem DESC");
            $stmt_pedidos->bind_param("i", $usuario_id);
            $stmt_pedidos->execute();
            $result_all = $stmt_pedidos->get_result();

            if ($result_all->num_rows > 0) {
                while($row = $result_all->fetch_assoc()) {
                    $status_class = $row['status'] == 'Concluído' ? 'opacity-50' : '';
                    $descricao_curta = strlen($row['descricao']) > 100 
                        ? substr($row['descricao'], 0, 100) . "..." 
                        : $row['descricao'];
                    
                    $avatar_seed = urlencode($row['autor_nome']);

                    // ALTERAÇÃO: Trocado 'col-lg-8 offset-lg-2' por 'col-lg-12' para ocupar a largura total
                    echo "<div class='col-lg-12'>";
                    echo "  <a href='pedido_detalhe.php?id=" . $row['id'] . "' class='pedido-link'>";
                    echo "      <div class='pedido " . $status_class . "'>";
                    
                    // NOVO BLOCO: Avatar e Nome do Autor
                    echo "          <div class='pedido-autor'>";
                    echo "              <img src='https://api.dicebear.com/8.x/initials/svg?seed=" . $avatar_seed . "' alt='Avatar de " . htmlspecialchars($row['autor_nome']) . "' class='autor-avatar'>";
                    echo "              <span class='autor-nome'>" . htmlspecialchars($row['autor_nome']) . "</span>";
                    echo "          </div>";
                    
                    // Conteúdo do pedido
                    echo "          <div class='pedido-content'>";
                    echo "              <span class='urgencia-tag " . strtolower(str_replace(' ', '-', $row['urgencia'])) . "'>" . htmlspecialchars($row['urgencia']) . "</span>";
                    echo "              <h3>" . htmlspecialchars($row['titulo']) . "</h3>";
                    echo "              <p><strong>Status:</strong> " . $row['status'] . "</p>";
                    echo "              <p>" . htmlspecialchars($descricao_curta) . "</p>";
                    echo "              <span class='ver-mais'>Ver ou gerenciar &rarr;</span>";
                    echo "          </div>";
                    echo "      </div>";
                    echo "  </a>";
                    echo "</div>";
                }
            } else {
                echo "<p class='no-pedidos'>Você ainda não cadastrou nenhum pedido. <a href='cadastrar.php'>Crie o primeiro!</a></p>";
            }
            $stmt_pedidos->close();
            ?>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const dadosCategoria = {
            labels: <?php echo json_encode($labels_cat); ?>,
            data: <?php echo json_encode($data_cat); ?>
        };
        const dadosUrgencia = {
            labels: <?php echo json_encode($labels_urg); ?>,
            data: <?php echo json_encode($data_urg); ?>
        };
    </script>
    
    <script src="../js/script.js" defer></script>
</body>
</html>