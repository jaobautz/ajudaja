<?php
// Garante que a conexão com o banco de dados exista
if (!isset($conn)) {
    include 'config.php';
}

// Lógica de busca e filtro
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$categoria_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$urgencia_filtro = isset($_GET['urgencia']) ? $_GET['urgencia'] : '';

// --- ALTERAÇÃO PRINCIPAL: Adicionado LEFT JOIN e COUNT para contar comentários ---
$sql = "SELECT p.*, u.nome as autor_nome, COUNT(c.id) as total_comentarios
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN comentarios c ON p.id = c.pedido_id
        WHERE p.status = 'Aberto'";
$params = [];
$param_count = 1;

if (!empty($busca)) {
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

// --- ALTERAÇÃO PRINCIPAL: Adicionado GROUP BY para a contagem funcionar ---
$sql .= " GROUP BY p.id, u.id ORDER BY p.data_postagem DESC";


if (@pg_query($conn, "DEALLOCATE list_pedidos")) {}
pg_prepare($conn, "list_pedidos", $sql);
$result = pg_execute($conn, "list_pedidos", $params);

if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $descricao_curta = strlen($row['descricao']) > 100 ? substr($row['descricao'], 0, 100) . "..." : $row['descricao'];
        $avatar_seed = urlencode($row['autor_nome']);
        
        $urgencia_class = strtolower(str_replace([' ', 'ã'], ['-', 'a'], $row['urgencia']));

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
        // --- NOVO ELEMENTO: Exibindo o contador de comentários ---
        echo "          <span class='ms-auto'><i data-lucide='message-square'></i> " . $row['total_comentarios'] . " Comentários</span>";
        echo "      </div>";
        echo "    </div>";
        echo "  </a>";
        echo "</div>";
    }
} else {
    echo "<div class='col-12 text-center card p-5'><p class='lead'>Nenhum pedido encontrado com os filtros selecionados.</p></div>";
}
?>