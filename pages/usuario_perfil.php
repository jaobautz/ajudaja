<?php
$page_title = 'Perfil do Usuário';
require_once '../includes/config.php'; 
require_once '../includes/session.php'; 
// Não precisa de autenticação, é uma página pública

$usuario_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$erro = null;

if (!$usuario_id) {
    $erro = "Usuário não encontrado.";
} else {
    // 1. Buscar dados do usuário
    $sql_user = "SELECT id, nome, data_cadastro FROM usuarios WHERE id = $1";
    if (!@pg_prepare($conn, "get_user_public_profile", $sql_user)) { die("Erro DB: get_user_public_profile"); }
    $result_user = pg_execute($conn, "get_user_public_profile", array($usuario_id));
    
    if (!$result_user || pg_num_rows($result_user) == 0) {
        $erro = "Usuário não encontrado.";
    } else {
        $usuario = pg_fetch_assoc($result_user);
        $page_title = "Perfil de " . htmlspecialchars($usuario['nome']); // Define título antes do header

        // 2. Buscar estatísticas de avaliação (o que ele RECEBEU)
        $sql_stats = "SELECT COUNT(*) as total_avaliacoes, AVG(nota) as media_notas 
                      FROM avaliacoes 
                      WHERE avaliado_id = $1"; // Onde ele foi o 'avaliado'
        if (!@pg_prepare($conn, "get_user_stats", $sql_stats)) { die("Erro DB: get_user_stats"); }
        $result_stats = pg_execute($conn, "get_user_stats", array($usuario_id));
        $stats = pg_fetch_assoc($result_stats);
        
        $total_avaliacoes = (int) $stats['total_avaliacoes'];
        $media_notas = (float) $stats['media_notas'];

        // 3. Buscar as avaliações recebidas (comentários)
        $sql_avaliacoes = "SELECT a.*, u_avaliador.nome as avaliador_nome 
                           FROM avaliacoes a
                           JOIN usuarios u_avaliador ON a.avaliador_id = u_avaliador.id
                           WHERE a.avaliado_id = $1
                           ORDER BY a.data_avaliacao DESC
                           LIMIT 10"; // Limita às 10 últimas
        if (!@pg_prepare($conn, "get_user_avaliacoes", $sql_avaliacoes)) { die("Erro DB: get_user_avaliacoes"); }
        $result_avaliacoes = pg_execute($conn, "get_user_avaliacoes", array($usuario_id));
        $avaliacoes = pg_fetch_all($result_avaliacoes) ?: [];

        // 4. Buscar pedidos criados por este usuário (apenas Abertos)
        $sql_pedidos = "SELECT id, titulo, categoria, urgencia 
                        FROM pedidos 
                        WHERE usuario_id = $1 AND status = 'Aberto'
                        ORDER BY data_postagem DESC
                        LIMIT 10"; // Limita aos 10 últimos
        if (!@pg_prepare($conn, "get_user_pedidos", $sql_pedidos)) { die("Erro DB: get_user_pedidos"); }
        $result_pedidos = pg_execute($conn, "get_user_pedidos", array($usuario_id));
        $pedidos_criados = pg_fetch_all($result_pedidos) ?: [];
    }
}

require_once '../includes/header.php'; 
?>

<main class="container my-5">
    <?php if ($erro): ?>
        <div class="alert alert-danger text-center"><?php echo $erro; ?></div>
    <?php else: 
        $avatar_seed = urlencode($usuario['nome']);
    ?>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card p-4 text-center" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                    <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avatar_seed; ?>" alt="Avatar" class="autor-avatar mx-auto" style="width: 100px; height: 100px; font-size: 2rem;">
                    <h2 class="mt-3 mb-1"><?php echo htmlspecialchars($usuario['nome']); ?></h2>
                    <p class="text-muted">Membro desde <?php echo date('M/Y', strtotime($usuario['data_cadastro'])); ?></p>
                    
                    <hr class="my-3">
                    
                    <h5 class="mb-3">Atividade como Voluntário</h5>
                    <?php if ($total_avaliacoes == 0): ?>
                        <p class="text-secondary">Este usuário ainda não recebeu avaliações.</p>
                    <?php else: ?>
                        <div class="display-4 fw-bold text-warning mb-2" title="<?php echo number_format($media_notas, 1); ?> de 5 estrelas">
                            <?php echo number_format($media_notas, 1); ?> <i data-lucide="star" style="width: 0.8em; height: 0.8em; fill: var(--warning-color);"></i>
                        </div>
                        <p class="text-muted"><?php echo $total_avaliacoes; ?> ajuda(s) confirmada(s)</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-8">
                <ul class="nav nav-tabs" id="perfilTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="avaliacoes-tab" data-bs-toggle="tab" data-bs-target="#avaliacoes" type="button" role="tab" aria-controls="avaliacoes" aria-selected="true">Avaliações Recebidas (<?php echo $total_avaliacoes; ?>)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button" role="tab" aria-controls="pedidos" aria-selected="false">Pedidos Criados (<?php echo count($pedidos_criados); ?>)</button>
                    </li>
                </ul>
                
                <div class="tab-content card" id="perfilTabsContent" style="border-top-left-radius: 0; box-shadow: var(--shadow-sm);">
                    <div class="tab-pane fade show active p-4" id="avaliacoes" role="tabpanel" aria-labelledby="avaliacoes-tab">
                        <?php if (empty($avaliacoes)): ?>
                            <p class="text-secondary text-center m-3">Nenhuma avaliação recebida ainda.</p>
                        <?php else: ?>
                            <?php foreach ($avaliacoes as $avaliacao): 
                                $avaliador_avatar = urlencode($avaliacao['avaliador_nome']);
                            ?>
                                <div class="comentario-card p-0" style="border-bottom: 1px solid var(--border-light-color); margin-bottom: 1rem; padding-bottom: 1rem;">
                                    <div class="comentario-avatar pt-2">
                                        <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avaliador_avatar; ?>" alt="Avatar">
                                    </div>
                                    <div class="comentario-body">
                                        <div class="comentario-header">
                                            <span class="comentario-autor"><?php echo htmlspecialchars($avaliacao['avaliador_nome']); ?></span>
                                            <span class="text-warning fw-bold"><?php echo $avaliacao['nota']; ?> <i data-lucide="star" style="width: 1em; height: 1em; fill: var(--warning-color);"></i></span>
                                        </div>
                                        <?php if (!empty($avaliacao['comentario_avaliacao'])): ?>
                                            <p class="comentario-texto fst-italic">"<?php echo nl2br(htmlspecialchars($avaliacao['comentario_avaliacao'])); ?>"</p>
                                        <?php else: ?>
                                            <p class="comentario-texto text-muted fst-italic">(O usuário não deixou um comentário)</p>
                                        <?php endif; ?>
                                        <span class="comentario-data"><?php echo date('d/m/Y', strtotime($avaliacao['data_avaliacao'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tab-pane fade p-4" id="pedidos" role="tabpanel" aria-labelledby="pedidos-tab">
                        <?php if (empty($pedidos_criados)): ?>
                            <p class="text-secondary text-center m-3">Este usuário não tem pedidos abertos no momento.</p>
                        <?php else: ?>
                             <div class="list-group list-group-flush">
                                <?php foreach ($pedidos_criados as $pedido): ?>
                                    <a href="pedido_detalhe.php?id=<?php echo $pedido['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($pedido['titulo']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($pedido['categoria']); ?> - <?php echo htmlspecialchars($pedido['urgencia']); ?></small>
                                        </div>
                                        <i data-lucide="chevron-right"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
if ($conn) { pg_close($conn); }
require_once '../includes/footer.php'; 
?>