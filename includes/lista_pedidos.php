<?php
// Não precisa de include 'config.php' se já estiver incluído na página que chama
if (!isset($conn)) {
    include 'config.php';
}

$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$urgencia_filtro = isset($_GET['urgencia']) ? $_GET['urgencia'] : '';

// MODIFICAÇÃO: Usamos um JOIN para buscar o nome do usuário junto com os dados do pedido
$sql = "SELECT p.*, u.nome as autor_nome 
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.status = 'Aberto'";

$params = [];
$types = "";

if ($busca) {
    $sql .= " AND (p.titulo LIKE ? OR p.descricao LIKE ? OR p.categoria LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "sss";
}
if ($urgencia_filtro) {
    $sql .= " AND p.urgencia = ?";
    $params[] = $urgencia_filtro;
    $types .= "s";
}
$sql .= " ORDER BY p.data_postagem DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Trunca a descrição para mostrar apenas um resumo
        $descricao_curta = strlen($row['descricao']) > 120 
            ? substr($row['descricao'], 0, 120) . "..." 
            : $row['descricao'];

        // Prepara o nome para usar na URL do avatar
        $avatar_seed = urlencode($row['autor_nome']);

        // ALTERAÇÃO: Trocado 'col-lg-8 offset-lg-2' por 'col-lg-12' para ocupar a largura total
        echo "<div class='col-lg-12'>";
        echo "  <a href='pedido_detalhe.php?id=" . $row['id'] . "' class='pedido-link'>";
        echo "    <div id='pedido-" . $row['id'] . "' class='pedido'>";
        
        // NOVO BLOCO: Avatar e Nome do Autor
        echo "      <div class='pedido-autor'>";
        echo "          <img src='https://api.dicebear.com/8.x/initials/svg?seed=" . $avatar_seed . "' alt='Avatar de " . htmlspecialchars($row['autor_nome']) . "' class='autor-avatar'>";
        echo "          <span class='autor-nome'>" . htmlspecialchars($row['autor_nome']) . "</span>";
        echo "      </div>";
        
        // Conteúdo do pedido
        echo "      <div class='pedido-content'>";
        echo "          <span class='urgencia-tag " . strtolower(str_replace(' ', '-', $row['urgencia'])) . "'>" . htmlspecialchars($row['urgencia']) . "</span>";
        echo "          <h3>" . htmlspecialchars($row['titulo']) . "</h3>";
        echo "          <p>" . htmlspecialchars($descricao_curta) . "</p>";
        echo "          <span class='ver-mais'>Ver detalhes &rarr;</span>";
        echo "      </div>";
        echo "    </div>";
        echo "  </a>";
        echo "</div>";
    }
} else {
    echo "<p class='no-pedidos'>Nenhum pedido encontrado com esses filtros. <a href='cadastrar.php'>Seja o primeiro a postar!</a></p>";
}
$stmt->close();
?>