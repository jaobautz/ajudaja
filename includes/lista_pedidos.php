<?php
// Garante que a conexão exista e BASE_URL esteja definida
if (!isset($conn) || !defined('BASE_URL')) {
    require_once 'config.php';
}

// Lógica de busca e filtro
$busca = trim($_GET['busca'] ?? '');
$categoria_filtro = trim($_GET['categoria'] ?? '');
$urgencia_filtro = trim($_GET['urgencia'] ?? '');
$estado_filtro = trim($_GET['estado'] ?? ''); // === GEOLOCALIZAÇÃO: Pega filtro Estado ===
$cidade_filtro = trim($_GET['cidade'] ?? ''); // === GEOLOCALIZAÇÃO: Pega filtro Cidade ===


// Query principal (sem alterações na estrutura de JOINs)
$sql = "SELECT p.*, u.nome as autor_nome, COUNT(c.id) as total_comentarios
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN comentarios c ON p.id = c.pedido_id
        WHERE p.status = 'Aberto'"; // Filtra sempre por Abertos na Home
$params = [];
$param_count = 1;

// Aplica os filtros recebidos via GET
if (!empty($busca)) {
    // Usamos parênteses para agrupar as condições OR da busca textual
    $sql .= " AND (p.titulo ILIKE $" . $param_count . " OR p.descricao ILIKE $" . $param_count . ")";
    $params[] = "%" . $busca . "%";
    $param_count++;
}
if (!empty($categoria_filtro)) {
    $sql .= " AND p.categoria = $" . $param_count++;
    $params[] = $categoria_filtro;
}
if (!empty($urgencia_filtro)) {
    $sql .= " AND p.urgencia = $" . $param_count++;
    $params[] = $urgencia_filtro;
}

// === GEOLOCALIZAÇÃO: Adiciona filtros de Estado e Cidade ===
if (!empty($estado_filtro)) {
    $sql .= " AND p.estado = $" . $param_count++;
    $params[] = $estado_filtro;
}
if (!empty($cidade_filtro)) {
    // Usamos ILIKE para busca case-insensitive e parcial (ex: "Paulo" encontra "São Paulo")
    $sql .= " AND p.cidade ILIKE $" . $param_count++; 
    $params[] = "%" . $cidade_filtro . "%";
}
// ==========================================================

$sql .= " GROUP BY p.id, u.id ORDER BY p.data_postagem DESC";
// Adicionar LIMIT/OFFSET aqui se quiser paginação na Home também

// Prepara e executa a query (sem alterações)
if (@pg_query($conn, "DEALLOCATE list_pedidos")) {}
if (!@pg_prepare($conn, "list_pedidos", $sql)) { die("Erro ao preparar query lista_pedidos: ".pg_last_error($conn));}
$result = @pg_execute($conn, "list_pedidos", $params);

if (!$result) {
     echo "<div class='col-12 text-center card p-5'><p class='lead text-danger'>Erro ao buscar pedidos: ".pg_last_error($conn)."</p></div>";
} elseif (pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        // Renderiza o card do pedido (sem alterações significativas, talvez adicionar cidade/estado?)
        $descricao_curta = strlen($row['descricao']) > 100 ? substr($row['descricao'], 0, 100) . "..." : $row['descricao'];
        $avatar_seed = urlencode($row['autor_nome']);
        $urgencia_class = strtolower(str_replace([' ', 'ã'], ['-', 'a'], $row['urgencia']));
        $tem_localizacao_card = !empty($row['cidade']) && !empty($row['estado']);

        echo "<div class='col-lg-12'>";
        echo "  <a href='pedido_detalhe.php?id=" . $row['id'] . "' class='pedido-link'>";
        echo "    <div class='pedido'>";
        echo "      <div class='pedido-autor'>";
        echo "          <img src='https://api.dicebear.com/8.x/initials/svg?seed=" . $avatar_seed . "' alt='Avatar' class='autor-avatar'>";
        echo "          <span class='autor-nome'>" . htmlspecialchars($row['autor_nome']) . "</span>";
        echo "      </div>";
        echo "      <h3>" . htmlspecialchars($row['titulo']) . "</h3>";
        echo "      <p class='text-secondary'>" . htmlspecialchars($descricao_curta) . "</p>";
        echo "      <div class='pedido-meta'>";
        echo "          <div class='tag-urgencia " . $urgencia_class . "'>" . htmlspecialchars($row['urgencia']) . "</div>";
        echo "          <span><i data-lucide='tag'></i>" . htmlspecialchars($row['categoria']) . "</span>";
        // Adiciona localização no card se disponível
        if($tem_localizacao_card) {
             echo "      <span><i data-lucide='map-pin'></i>" . htmlspecialchars($row['cidade']) . ' - ' . htmlspecialchars($row['estado']) . "</span>";
        }
        echo "          <span class='ms-auto'><i data-lucide='message-square'></i> " . $row['total_comentarios'] . "</span>";
        echo "      </div>";
        echo "    </div>";
        echo "  </a>";
        echo "</div>";
    }
} else {
    echo "<div class='col-12 text-center card p-5'><p class='lead'>Nenhum pedido encontrado com os filtros selecionados.</p></div>";
}
?>