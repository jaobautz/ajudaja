<?php
$page_title = 'Meu Painel - AjudaJá';
$include_chartjs = true;
require_once '../includes/config.php'; 
require_once '../includes/autenticacao.php'; 
require_once '../includes/session.php'; 

$usuario_id = $_SESSION['usuario_id'];

// =======================================================
// LÓGICA DE BUSCA ESTATÍSTICAS (GRÁFICOS) - Sem alterações
// =======================================================
function pg_fetch_count($conn, $query_name, $sql, $params) {
    if (@pg_query($conn, "DEALLOCATE {$query_name}")) {}
    pg_prepare($conn, $query_name, $sql);
    $result = pg_execute($conn, $query_name, $params);
    return ($result && pg_num_rows($result) > 0) ? (int)pg_fetch_result($result, 0, 0) : 0;
}
$total = pg_fetch_count($conn, "count_total", "SELECT COUNT(*) FROM pedidos WHERE usuario_id = $1", array($usuario_id));
$urgentes = pg_fetch_count($conn, "count_urgentes", "SELECT COUNT(*) FROM pedidos WHERE usuario_id = $1 AND urgencia='Urgente'", array($usuario_id));
$abertos = pg_fetch_count($conn, "count_abertos", "SELECT COUNT(*) FROM pedidos WHERE usuario_id = $1 AND status='Aberto'", array($usuario_id));
$concluidos = $total - $abertos;
$dados_categoria = ['labels' => [], 'data' => []]; $sql_cat = "SELECT categoria, COUNT(*) as total FROM pedidos WHERE usuario_id = $1 GROUP BY categoria ORDER BY total DESC"; if (@pg_query($conn, "DEALLOCATE count_by_cat")) {} pg_prepare($conn, "count_by_cat", $sql_cat); $result_cat = pg_execute($conn, "count_by_cat", array($usuario_id)); if ($result_cat) { while($row = pg_fetch_assoc($result_cat)) { $dados_categoria['labels'][] = $row['categoria']; $dados_categoria['data'][] = (int)$row['total']; } }
$dados_urgencia = ['labels' => [], 'data' => []]; $sql_urg = "SELECT urgencia, COUNT(*) as total FROM pedidos WHERE usuario_id = $1 GROUP BY urgencia ORDER BY urgencia"; if (@pg_query($conn, "DEALLOCATE count_by_urg")) {} pg_prepare($conn, "count_by_urg", $sql_urg); $result_urg = pg_execute($conn, "count_by_urg", array($usuario_id)); if ($result_urg) { while($row = pg_fetch_assoc($result_urg)) { $dados_urgencia['labels'][] = $row['urgencia']; $dados_urgencia['data'][] = (int)$row['total']; } }


// =======================================================
// LÓGICA DE FILTRO E PAGINAÇÃO (MEUS PEDIDOS) - Sem alterações
// =======================================================
$limit = 10; $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT); if (!$page || $page < 1) { $page = 1; } $offset = ($page - 1) * $limit;
$busca = trim($_GET['busca'] ?? ''); $status_filtro = trim($_GET['status'] ?? '');
$sql_count = "SELECT COUNT(*) FROM pedidos WHERE usuario_id = $1"; $params_count = [$usuario_id]; $param_count_c = 2; if (!empty($busca)) { $sql_count .= " AND (titulo ILIKE $" . $param_count_c++ . " OR descricao ILIKE $" . $param_count_c++ . ")"; $params_count[] = "%$busca%"; $params_count[] = "%$busca%"; } if (!empty($status_filtro)) { $sql_count .= " AND status = $" . $param_count_c++; $params_count[] = $status_filtro; }
$total_pedidos_filtrados = pg_fetch_count($conn, "count_dashboard_pedidos", $sql_count, $params_count); $total_paginas = ceil($total_pedidos_filtrados / $limit);
$sql_pedidos = "SELECT p.*, u.nome as autor_nome, COUNT(c.id) as total_comentarios FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id LEFT JOIN comentarios c ON p.id = c.pedido_id WHERE p.usuario_id = $1"; $params = [$usuario_id]; $param_count = 2; if (!empty($busca)) { $sql_pedidos .= " AND (p.titulo ILIKE $" . $param_count++ . " OR p.descricao ILIKE $" . $param_count++ . ")"; $params[] = "%$busca%"; $params[] = "%$busca%"; } if (!empty($status_filtro)) { $sql_pedidos .= " AND p.status = $" . $param_count++; $params[] = $status_filtro; } $sql_pedidos .= " GROUP BY p.id, u.id ORDER BY p.data_postagem DESC LIMIT $" . $param_count++ . " OFFSET $" . $param_count++; $params[] = $limit; $params[] = $offset;
if (@pg_query($conn, "DEALLOCATE list_dashboard_pedidos")) {} pg_prepare($conn, "list_dashboard_pedidos", $sql_pedidos); $result_all = pg_execute($conn, "list_dashboard_pedidos", $params);
function construir_url_paginacao($pagina, $busca, $status) { $query_params = ['page' => $pagina, 'busca' => $busca, 'status' => $status]; $query_params = array_filter($query_params); return 'dashboard.php?' . http_build_query($query_params); }


// =======================================================
// LÓGICA PARA SEÇÕES DO VOLUNTÁRIO - Sem alterações
// =======================================================
$sql_conversas_iniciadas = "SELECT c.id as conversa_id, p.titulo as pedido_titulo, p.id as pedido_id, u_criador.nome as criador_nome FROM conversas c JOIN pedidos p ON c.pedido_id = p.id JOIN usuarios u_criador ON c.usuario_criador_id = u_criador.id WHERE c.usuario_voluntario_id = $1 ORDER BY c.data_criacao DESC LIMIT 10";
if (@pg_query($conn, "DEALLOCATE get_conversas_iniciadas")) {} pg_prepare($conn, "get_conversas_iniciadas", $sql_conversas_iniciadas); $result_conversas = pg_execute($conn, "get_conversas_iniciadas", array($usuario_id)); $conversas_iniciadas = []; if ($result_conversas) { while ($row = pg_fetch_assoc($result_conversas)) { $conversas_iniciadas[] = $row; } }
$sql_pedidos_comentados_ids = "SELECT DISTINCT pedido_id FROM comentarios WHERE usuario_id = $1 ORDER BY pedido_id DESC LIMIT 10";
if (@pg_query($conn, "DEALLOCATE get_pedidos_comentados_ids")) {} pg_prepare($conn, "get_pedidos_comentados_ids", $sql_pedidos_comentados_ids); $result_ids = pg_execute($conn, "get_pedidos_comentados_ids", array($usuario_id)); $pedidos_comentados = []; if ($result_ids && pg_num_rows($result_ids) > 0) { $pedido_ids = pg_fetch_all_columns($result_ids); $in_clause = implode(',', array_map('intval', $pedido_ids)); $sql_pedidos_comentados = "SELECT p.id, p.titulo, u.nome as autor_nome FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id IN ({$in_clause})"; $result_pedidos_com = pg_query($conn, $sql_pedidos_comentados); if ($result_pedidos_com) { while ($row = pg_fetch_assoc($result_pedidos_com)) { $pedidos_comentados[] = $row; } } }

?>

<?php require_once '../includes/header.php'; ?>

<main class="container my-5">
    <div class="text-center mb-5">
        <h1 class="display-6 fw-bold">Meu Painel de Controle</h1>
        <p class="lead text-secondary">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>! Acompanhe suas estatísticas, gerencie seus pedidos e interações.</p>
    </div>
    
    <?php if (isset($_SESSION['sucesso'])) { echo "<div class='alert alert-success'>".$_SESSION['sucesso']."</div>"; unset($_SESSION['sucesso']); } ?>
    <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>".$_SESSION['erro']."</div>"; unset($_SESSION['erro']); } ?>

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
    <form action="dashboard.php" method="GET" class="row mb-4 g-3 align-items-end filter-form">
        <div class="col-md-7"><label for="busca" class="form-label">Buscar nos meus pedidos</label><input type="text" class="form-control" id="busca" name="busca" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Ex: cesta básica, vazamento..."></div>
        <div class="col-md-3"><label for="status_filtro" class="form-label">Status</label><select class="form-select" id="status_filtro" name="status"><option value="">Todos</option><option value="Aberto" <?php echo ($status_filtro == 'Aberto') ? 'selected' : ''; ?>>Abertos</option><option value="Concluído" <?php echo ($status_filtro == 'Concluído') ? 'selected' : ''; ?>>Concluídos</option></select></div>
        <div class="col-md-2"><button type="submit" class="btn btn-success w-100"><i data-lucide="search"></i> Filtrar</button></div>
    </form>
    <div id="lista-pedidos-dash" class="row">
        <?php if ($result_all && pg_num_rows($result_all) > 0): ?>
            <?php while($row = pg_fetch_assoc($result_all)): 
                $status_class = $row['status'] == 'Concluído' ? 'opacity-50' : '';
                $avatar_seed = urlencode($row['autor_nome']);
            ?>
                <div class='col-lg-12' id='pedido-dash-<?php echo $row['id']; ?>'>
                    <div class='pedido <?php echo $status_class; ?>'>
                        <div class='d-flex justify-content-between align-items-center mb-3'>
                            <div class='pedido-autor'><img src='https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avatar_seed; ?>' alt='Avatar' class='autor-avatar'><span class='autor-nome'><?php echo htmlspecialchars($row['autor_nome']); ?></span></div>
                            <span class='badge bg-<?php echo ($row['status'] == 'Aberto' ? 'success' : 'secondary'); ?>'><?php echo $row['status']; ?></span>
                        </div>
                        <a href='pedido_detalhe.php?id=<?php echo $row['id']; ?>' class='pedido-link-titulo'><h3><?php echo htmlspecialchars($row['titulo']); ?></h3></a>
                        <div class='pedido-meta border-top pt-3 mt-3 d-flex justify-content-between align-items-center'>
                            <span><i data-lucide='message-square'></i> <?php echo $row['total_comentarios']; ?> Comentários</span>
                            <div class='d-flex gap-2'>
                                <?php if ($row['status'] == 'Aberto'): ?><button class='btn btn-sm btn-success' onclick='marcarConcluido(<?php echo $row['id']; ?>)'><i data-lucide='check-circle'></i> Concluir</button><?php endif; ?>
                                <a href='editar_pedido.php?id=<?php echo $row['id']; ?>' class='btn btn-sm btn-outline-primary'><i data-lucide='edit-2'></i> Editar</a>
                                <form action='../includes/excluir_pedido.php' method='POST' onsubmit='return confirm("Tem certeza?");' style='display:inline;'><input type='hidden' name='csrf_token' value='<?php echo $_SESSION['csrf_token']; ?>'><input type='hidden' name='pedido_id' value='<?php echo $row['id']; ?>'><button type='submit' class='btn btn-sm btn-outline-danger'><i data-lucide='trash-2'></i> Excluir</button></form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php elseif ($total > 0 && $total_pedidos_filtrados == 0): ?>
             <div class='col-12 text-center card p-5'><p class='lead'>Nenhum pedido encontrado com os filtros aplicados.</p><a href='dashboard.php' class='btn btn-success mt-3 mx-auto' style='width: auto;'>Limpar Filtros</a></div>
        <?php else: ?>
             <div class='col-12 text-center card p-5'><p class='lead'>Você ainda não cadastrou nenhum pedido.</p><a href='cadastrar.php' class='btn btn-success mt-3 mx-auto' style='width: auto;'><i data-lucide='plus-circle'></i> Criar meu primeiro pedido</a></div>
        <?php endif; ?>
    </div>
    
    <?php if ($total_paginas > 1): ?>
    <nav aria-label="Paginação Dashboard" class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo construir_url_paginacao($page - 1, $busca, $status_filtro); ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo construir_url_paginacao($i, $busca, $status_filtro); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($page >= $total_paginas) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo construir_url_paginacao($page + 1, $busca, $status_filtro); ?>">Próxima</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    <hr class="my-5"> 

    <div class="row g-5">
        <div class="col-md-6">
            <h4 class="text-center mb-4">Conversas Iniciadas por Mim</h4>
            <div class="card p-3" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <?php if (empty($conversas_iniciadas)): ?>
                    <p class="text-center text-secondary m-4">Você ainda não iniciou nenhuma conversa.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($conversas_iniciadas as $conversa): 
                            $avatar_seed_criador = urlencode($conversa['criador_nome']);
                        ?>
                            <a href="chat.php?conversa_id=<?php echo $conversa['conversa_id']; ?>" class="list-group-item list-group-item-action px-2 py-3">
                                <div class="d-flex w-100 align-items-center">
                                    <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avatar_seed_criador; ?>" alt="Avatar" class="autor-avatar me-3">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Com <?php echo htmlspecialchars($conversa['criador_nome']); ?></h6>
                                        <p class="mb-0 text-secondary" style="font-size: 0.9rem;">Sobre: "<?php echo htmlspecialchars(substr($conversa['pedido_titulo'], 0, 30)) . (strlen($conversa['pedido_titulo']) > 30 ? '...' : ''); ?>"</p>
                                    </div>
                                    <i data-lucide="chevron-right" class="text-secondary ms-2"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <h4 class="text-center mb-4">Pedidos Onde Comentei</h4>
            <div class="card p-3" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <?php if (empty($pedidos_comentados)): ?>
                    <p class="text-center text-secondary m-4">Você ainda não comentou em nenhum pedido.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pedidos_comentados as $pedido_com): 
                             $avatar_seed_autor = urlencode($pedido_com['autor_nome']);
                        ?>
                            <a href="pedido_detalhe.php?id=<?php echo $pedido_com['id']; ?>" class="list-group-item list-group-item-action px-2 py-3">
                                <div class="d-flex w-100 align-items-center">
                                     <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avatar_seed_autor; ?>" alt="Avatar" class="autor-avatar me-3">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($pedido_com['titulo']); ?></h6>
                                        <p class="mb-0 text-secondary" style="font-size: 0.9rem;">Pedido de: <?php echo htmlspecialchars($pedido_com['autor_nome']); ?></p>
                                    </div>
                                    <i data-lucide="chevron-right" class="text-secondary ms-2"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div> 

</main>

<script>
    const dadosCategoria = <?php echo json_encode($dados_categoria); ?>;
    const dadosUrgencia = <?php echo json_encode($dados_urgencia); ?>;
</script>

<?php 
// Garante fechamento da conexão antes do footer
if ($conn) { pg_close($conn); }
require_once '../includes/footer.php'; 
?>