<?php
// Garante que a conexão e dependências existam
if (!isset($conn) || !defined('BASE_URL')) {
    require_once 'config.php';
}
require_once 'geocoding.php'; // Inclui nosso serviço de geocodificação

// Coleta filtros
$busca = trim($_GET['busca'] ?? '');
$categoria_filtro = trim($_GET['categoria'] ?? '');
$urgencia_filtro = trim($_GET['urgencia'] ?? '');
$cep_filtro = trim($_GET['filtro_cep'] ?? '');
$raio_filtro_km = filter_input(INPUT_GET, 'filtro_raio', FILTER_VALIDATE_FLOAT);

// Variáveis para o filtro de geolocalização
$filtro_geo_aplicado = false;
$user_lat = null;
$user_lon = null;
$distancia_sql = ""; // Parte da query SELECT
$where_geo = "";     // Parte da query WHERE
$order_by = "ORDER BY p.data_postagem DESC"; // Ordem padrão

// --- ETAPA A: Tenta Geocodificar o CEP do Usuário ---
if (!empty($cep_filtro) && !empty($raio_filtro_km)) {
    $cep_limpo = preg_replace('/[^0-9]/', '', $cep_filtro);
    $geoDataUsuario = getGeoDataFromCEP($cep_limpo);
    
    if ($geoDataUsuario && !empty($geoDataUsuario['latitude']) && !empty($geoDataUsuario['longitude'])) {
        $user_lat = $geoDataUsuario['latitude'];
        $user_lon = $geoDataUsuario['longitude'];
        $filtro_geo_aplicado = true;
    } else {
        // CEP inválido ou não encontrado pela API
        echo "<div class='col-12'><div class='alert alert-warning'>Não foi possível encontrar a localização para o CEP informado. Exibindo todos os pedidos.</div></div>";
    }
} elseif (!empty($cep_filtro) && empty($raio_filtro_km)) {
    echo "<div class='col-12'><div class='alert alert-warning'>Por favor, selecione um raio de distância para filtrar por CEP.</div></div>";
}

// --- ETAPA B: Monta a Query SQL ---
$params = [];
$param_count = 1;

if ($filtro_geo_aplicado) {
    // === Query Otimizada com Filtro de Distância ===
    // (point(lon, lat) <@> point(lon, lat)) calcula a distância em milhas
    // Multiplicamos por 1.609344 para converter para KM
    // Usamos ll_to_earth() para trabalhar com o índice GIST
    
    // Parâmetros para a distância (lon, lat, lon, lat, raio_em_metros)
    $params[] = $user_lon; $params[] = $user_lat;
    $raio_em_metros = $raio_filtro_km * 1000;
    
    // Calcula a distância usando earth_distance (requer extensão)
    // ll_to_earth(lat, lon)
    $distancia_sql = ", (earth_distance(ll_to_earth(p.latitude, p.longitude), ll_to_earth($".($param_count++).", $".($param_count++).")) / 1000.0) AS distancia_km";
    
    // Filtra usando a mesma função (o índice GIST acelera isso)
    $params[] = $user_lat; $params[] = $user_lon; $params[] = $raio_em_metros;
    $where_geo = " AND earth_distance(ll_to_earth(p.latitude, p.longitude), ll_to_earth($".($param_count++).", $".($param_count++).")) <= $".($param_count++);
    
    $order_by = "ORDER BY distancia_km ASC"; // Ordena do mais próximo ao mais distante
    
} else {
    // === Query Padrão (sem filtro de distância) ===
    $distancia_sql = ", NULL AS distancia_km"; // Coluna placeholder
}

// --- ETAPA C: Monta a Query Completa ---
$sql = "SELECT p.*, u.nome as autor_nome, COUNT(c.id) as total_comentarios
        {$distancia_sql}
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN comentarios c ON p.id = c.pedido_id
        WHERE p.status = 'Aberto'
        {$where_geo}"; // Adiciona o filtro de distância (se aplicável)

// Aplica os filtros de texto, categoria e urgência
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
// Pedidos sem lat/lon não devem aparecer no filtro de distância
if ($filtro_geo_aplicado) {
    $sql .= " AND p.latitude IS NOT NULL AND p.longitude IS NOT NULL";
}

$sql .= " GROUP BY p.id, u.id {$order_by}";
// Adicionar LIMIT/OFFSET aqui se quiser paginação

// Prepara e executa
if (@pg_query($conn, "DEALLOCATE list_pedidos_geo")) {}
if (!@pg_prepare($conn, "list_pedidos_geo", $sql)) { die("Erro ao preparar query lista_pedidos_geo: ".pg_last_error($conn));}
$result = @pg_execute($conn, "list_pedidos_geo", $params);

// --- ETAPA D: Exibe os Resultados ---
if (!$result) {
     echo "<div class='col-12 text-center card p-5'><p class='lead text-danger'>Erro ao buscar pedidos: ".pg_last_error($conn)."</p></div>";
} elseif (pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $descricao_curta = strlen($row['descricao']) > 100 ? substr($row['descricao'], 0, 100) . "..." : $row['descricao'];
        $avatar_seed = urlencode($row['autor_nome']);
        $urgencia_class = strtolower(str_replace([' ', 'ã'], ['-', 'a'], $row['urgencia']));
        
        // Formata a distância (se houver)
        $distancia_formatada = null;
        if ($filtro_geo_aplicado && isset($row['distancia_km'])) {
            $distancia_formatada = "Aprox. " . number_format($row['distancia_km'], 1, ',', '.') . " km";
        }

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
        
        // Exibe a localização (Cidade/Estado) OU a distância calculada
        if($distancia_formatada) {
             echo "      <span class_='fw-bold text-success'><i data-lucide='map-pin'></i>" . htmlspecialchars($distancia_formatada) . "</span>";
        } elseif (!empty($row['cidade']) && !empty($row['estado'])) {
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