<?php
$page_title = 'Meu Painel - AjudaJá';
$include_chartjs = true; // Habilita o script do Chart.js no footer
include '../includes/config.php';
include '../includes/autenticacao.php';

$usuario_id = $_SESSION['usuario_id'];

// Função auxiliar para executar consultas de contagem
function pg_fetch_count($conn, $query_name, $sql, $params) {
    if (@pg_query($conn, "DEALLOCATE {$query_name}")) {}
    pg_prepare($conn, $query_name, $sql);
    $result = pg_execute($conn, $query_name, $params);
    return ($result && pg_num_rows($result) > 0) ? (int)pg_fetch_result($result, 0, 0) : 0;
}

// 1. DADOS PARA OS CARDS DE ESTATÍSTICAS
$total = pg_fetch_count($conn, "count_total", "SELECT COUNT(*) FROM pedidos WHERE usuario_id = $1", array($usuario_id));
$urgentes = pg_fetch_count($conn, "count_urgentes", "SELECT COUNT(*) FROM pedidos WHERE usuario_id = $1 AND urgencia='Urgente'", array($usuario_id));
$abertos = pg_fetch_count($conn, "count_abertos", "SELECT COUNT(*) FROM pedidos WHERE usuario_id = $1 AND status='Aberto'", array($usuario_id));
$concluidos = $total - $abertos;

// 2. DADOS PARA O GRÁFICO DE CATEGORIAS
$dados_categoria = ['labels' => [], 'data' => []];
$sql_cat = "SELECT categoria, COUNT(*) as total FROM pedidos WHERE usuario_id = $1 GROUP BY categoria ORDER BY total DESC";
if (@pg_query($conn, "DEALLOCATE count_by_cat")) {}
pg_prepare($conn, "count_by_cat", $sql_cat);
$result_cat = pg_execute($conn, "count_by_cat", array($usuario_id));
if ($result_cat) {
    while($row = pg_fetch_assoc($result_cat)) {
        $dados_categoria['labels'][] = $row['categoria'];
        $dados_categoria['data'][] = (int)$row['total'];
    }
}

// 3. DADOS PARA O GRÁFICO DE URGÊNCIA
$dados_urgencia = ['labels' => [], 'data' => []];
$sql_urg = "SELECT urgencia, COUNT(*) as total FROM pedidos WHERE usuario_id = $1 GROUP BY urgencia ORDER BY urgencia";
if (@pg_query($conn, "DEALLOCATE count_by_urg")) {}
pg_prepare($conn, "count_by_urg", $sql_urg);
$result_urg = pg_execute($conn, "count_by_urg", array($usuario_id));
if ($result_urg) {
    while($row = pg_fetch_assoc($result_urg)) {
        $dados_urgencia['labels'][] = $row['urgencia'];
        $dados_urgencia['data'][] = (int)$row['total'];
    }
}

?>

<?php include '../includes/header.php'; ?>

<main class="container my-5">
    <div class="text-center mb-5">
        <h1 class="display-6 fw-bold">Meu Painel de Controle</h1>
        <p class="lead text-secondary">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>! Acompanhe suas estatísticas e gerencie seus pedidos.</p>
    </div>
    
    <div class="row mb-5 g-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card h-100">
                <div class="stat-card-icon" style="background-color: #3B82F6;"><i data-lucide="list"></i></div>
                <h5 class="stat-card-title">Total de Pedidos</h5>
                <p class="stat-card-number"><?php echo $total; ?></p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card h-100">
                <div class="stat-card-icon" style="background-color: var(--danger-color);"><i data-lucide="siren"></i></div>
                <h5 class="stat-card-title">Urgentes</h5>
                <p class="stat-card-number"><?php echo $urgentes; ?></p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
             <div class="stat-card h-100">
                <div class="stat-card-icon" style="background-color: var(--warning-color);"><i data-lucide="clock"></i></div>
                <h5 class="stat-card-title">Abertos</h5>
                <p class="stat-card-number"><?php echo $abertos; ?></p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card h-100">
                <div class="stat-card-icon" style="background-color: var(--primary-color);"><i data-lucide="check-circle-2"></i></div>
                <h5 class="stat-card-title">Concluídos</h5>
                <p class="stat-card-number"><?php echo $concluidos; ?></p>
            </div>
        </div>
    </div>
    
    <?php if ($total > 0): ?>
    <div class="row mb-5 g-4">
        <div class="col-md-6">
            <div class="card h-100" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                <div class="card-body">
                    <h5 class="card-title text-center mb-3">Pedidos por Categoria</h5>
                    <canvas id="grafico-categoria"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                <div class="card-body">
                    <h5 class="card-title text-center mb-3">Pedidos por Urgência</h5>
                    <canvas id="grafico-urgencia"></canvas>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <h3 class="text-center mb-4">Gerenciar Meus Pedidos</h3>
    <div id="lista-pedidos-dash" class="row">
        <?php
        // --- ALTERAÇÃO PRINCIPAL: Query agora conta os comentários ---
        $sql_pedidos = "SELECT p.*, u.nome as autor_nome, COUNT(c.id) as total_comentarios
                        FROM pedidos p 
                        JOIN usuarios u ON p.usuario_id = u.id
                        LEFT JOIN comentarios c ON p.id = c.pedido_id
                        WHERE p.usuario_id = $1 
                        GROUP BY p.id, u.id
                        ORDER BY p.data_postagem DESC";
        
        if (@pg_query($conn, "DEALLOCATE list_dashboard_pedidos")) {}
        pg_prepare($conn, "list_dashboard_pedidos", $sql_pedidos);
        $result_all = pg_execute($conn, "list_dashboard_pedidos", array($usuario_id));

        if ($result_all && pg_num_rows($result_all) > 0) {
            while($row = pg_fetch_assoc($result_all)) {
                $status_class = $row['status'] == 'Concluído' ? 'opacity-50' : '';
                $avatar_seed = urlencode($row['autor_nome']);
                
                echo "<div class='col-lg-12'>";
                echo "  <a href='pedido_detalhe.php?id=" . $row['id'] . "' class='pedido-link'>";
                echo "      <div class='pedido " . $status_class . "'>";
                echo "          <div class='d-flex justify-content-between align-items-center mb-3'>";
                echo "              <div class='pedido-autor'>";
                echo "                  <img src='https://api.dicebear.com/8.x/initials/svg?seed=" . $avatar_seed . "' alt='Avatar' class='autor-avatar'>";
                echo "                  <span class='autor-nome'>" . htmlspecialchars($row['autor_nome']) . "</span>";
                echo "              </div>";
                echo "              <span class='badge bg-" . ($row['status'] == 'Aberto' ? 'success' : 'secondary') . "'>" . $row['status'] . "</span>";
                echo "          </div>";
                echo "          <h3>" . htmlspecialchars($row['titulo']) . "</h3>";
                // --- NOVO ELEMENTO: Exibindo o contador de comentários ---
                echo "          <div class='pedido-meta border-top pt-3 mt-3'>";
                echo "              <span><i data-lucide='message-square'></i> " . $row['total_comentarios'] . " Comentários</span>";
                echo "          </div>";
                echo "      </div>";
                echo "  </a>";
                echo "</div>";
            }
        } else {
            echo "<div class='col-12 text-center card p-5' style='border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);'>";
            echo "  <p class='lead'>Você ainda não cadastrou nenhum pedido.</p>";
            echo "  <a href='cadastrar.php' class='btn btn-success mt-3 mx-auto' style='width: auto;'><i data-lucide='plus-circle'></i> Criar meu primeiro pedido</a>";
            echo "</div>";
        }
        pg_close($conn);
        ?>
    </div>
</main>

<script>
    const dadosCategoria = <?php echo json_encode($dados_categoria); ?>;
    const dadosUrgencia = <?php echo json_encode($dados_urgencia); ?>;
</script>

<?php include '../includes/footer.php'; ?>