<?php
$page_title = 'Detalhes do Pedido - AjudaJá';
require_once '../includes/config.php';
require_once '../includes/session.php';

$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$erro = null;
$pedido = null;
$comentarios = [];
$comentarios_arvore = [];

if (!$pedido_id) {
    $erro = "Pedido não encontrado ou ID inválido.";
} else {
    // Busca detalhes do pedido E localização
    $sql_pedido = "SELECT p.*, u.nome as autor_nome, p.cidade, p.estado
                   FROM pedidos p
                   JOIN usuarios u ON p.usuario_id = u.id
                   WHERE p.id = $1";

    if (!@pg_prepare($conn, "get_pedido_detalhe", $sql_pedido)) { die("Erro ao preparar consulta do pedido."); }
    $result_pedido = pg_execute($conn, "get_pedido_detalhe", array($pedido_id));

    if ($result_pedido && pg_num_rows($result_pedido) === 1) {
        $pedido = pg_fetch_assoc($result_pedido);
        $page_title = htmlspecialchars($pedido['titulo']); // Define título antes do header

        // Busca comentários e reputação do autor
        $sql_comentarios = "SELECT c.*, u.nome as autor_nome, u.reputacao as autor_reputacao FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.pedido_id = $1 ORDER BY c.data_comentario ASC";
        if (!@pg_prepare($conn, "get_comentarios", $sql_comentarios)) { die("Erro ao preparar consulta de comentários."); }
        $result_comentarios = pg_execute($conn, "get_comentarios", array($pedido_id));
        if ($result_comentarios) {
            // Organiza comentários em árvore
            while ($row = pg_fetch_assoc($result_comentarios)) { $comentarios[$row['id']] = $row; }
            foreach ($comentarios as $id => &$comentario) {
                if ($comentario['parent_id'] === null) { $comentarios_arvore[$id] = &$comentario; }
                else { if (isset($comentarios[$comentario['parent_id']])) { $comentarios[$comentario['parent_id']]['respostas'][$id] = &$comentario; } }
            }
            unset($comentario);
        }

    } else {
        $erro = "Pedido não encontrado ou indisponível.";
    }
}

// Função para Renderizar Comentários (Definida uma única vez)
function renderizar_comentarios($comentarios_nivel, $nivel = 0) {
    // ... (código da função renderizar_comentarios permanece o mesmo) ...
     if (empty($comentarios_nivel)) { return; }
    $max_nivel = 5; $nivel_atual = min($nivel, $max_nivel);
    echo "<div class='list-group list-group-flush ms-". ($nivel_atual * 2) ."'>";
    foreach ($comentarios_nivel as $comentario) {
        $comentario_avatar_seed = urlencode($comentario['autor_nome']);
        $comentario_id = $comentario['id'];
        $autor_reputacao = $comentario['autor_reputacao'] ?? 0;

        echo "<div class='list-group-item px-0 py-3 comentario-item' id='comentario-{$comentario_id}'>";
        echo "<div class='d-flex align-items-start'>";
        echo "<img src='https://api.dicebear.com/8.x/initials/svg?seed={$comentario_avatar_seed}' alt='Avatar' class='autor-avatar me-3'>";
        echo "<div class='flex-grow-1 comentario-body'>";
        echo "<div class='comentario-header d-flex justify-content-between align-items-center mb-1'>";
        echo "<span class='comentario-autor fw-bold'>".htmlspecialchars($comentario['autor_nome']);
        if ($autor_reputacao > 0) { echo " <span class='badge bg-warning text-dark rounded-pill' title='Pontos de Reputação'><i data-lucide='award' style='width: 0.8em; height: 0.8em; vertical-align: -0.1em;'></i> {$autor_reputacao}</span>"; }
        echo "</span>";
        echo "<span class='comentario-data text-muted' style='font-size: 0.8rem;'>".date('d/m/Y H:i', strtotime($comentario['data_comentario']))."</span>";
        echo "</div>";
        echo "<p class='comentario-texto mb-2'>".nl2br(htmlspecialchars($comentario['comentario']))."</p>";
        if (isset($_SESSION['usuario_id'])) { echo "<button class='btn btn-sm btn-link text-secondary p-0 btn-responder' data-comment-id='{$comentario_id}' style='text-decoration: none;'><i data-lucide='message-circle' style='width: 1em; height: 1em; vertical-align: -0.125em;'></i> Responder</button>"; }
        echo "</div></div>";
        if (isset($_SESSION['usuario_id'])) {
             echo "<div class='resposta-form mt-3 ps-5' id='form-resposta-{$comentario_id}' style='display: none;'>";
             echo "<form action='../includes/processa_comentario.php' method='POST' class='form-comentario'><input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'><input type='hidden' name='pedido_id' value='{$comentario['pedido_id']}'><input type='hidden' name='parent_id' value='{$comentario_id}'><div class='mb-2'><textarea class='form-control form-control-sm' name='comentario' rows='2' placeholder='Sua resposta...' required></textarea></div><div class='text-end'><button type='button' class='btn btn-sm btn-outline-secondary btn-cancelar-resposta' data-comment-id='{$comentario_id}'>Cancelar</button><button type='submit' class='btn btn-sm btn-success'><i data-lucide='send'></i> Enviar</button></div></form>";
             echo "</div>";
        }
        if (!empty($comentario['respostas'])) { renderizar_comentarios($comentario['respostas'], $nivel + 1); }
        echo "</div>";
    }
    echo "</div>";
}


// Inclui o header
require_once '../includes/header.php';
?>

<main class="container my-5">
    <?php if (isset($_SESSION['sucesso'])) { echo "<div class='alert alert-success'>".$_SESSION['sucesso']."</div>"; unset($_SESSION['sucesso']); } ?>
    <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>".$_SESSION['erro']."</div>"; unset($_SESSION['erro']); } ?>

    <?php if ($erro): ?>
        <div class="alert alert-danger text-center"><?php echo $erro; ?></div>
    <?php elseif ($pedido):
        $avatar_seed = urlencode($pedido['autor_nome']);
        $urgencia_class = strtolower(str_replace([' ', 'ã'], ['-', 'a'], $pedido['urgencia']));
        $e_dono = (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $pedido['usuario_id']);
        $tem_localizacao = !empty($pedido['cidade']) && !empty($pedido['estado']);
    ?>
        <div class="detalhe-container">

            <div class="pedido-autor mb-4 pb-3 border-bottom border-light"> <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avatar_seed; ?>" alt="Avatar" class="autor-avatar">
                <div>
                    <span class="autor-nome d-block"><?php echo htmlspecialchars($pedido['autor_nome']); ?></span>
                    <span class="text-muted" style="font-size: 0.85rem;">Criador(a) do Pedido</span>
                </div>
            </div>

            <div class="detalhe-header mb-3">
                <h1><?php echo htmlspecialchars($pedido['titulo']); ?></h1>
            </div>

            <div class="detalhe-meta d-flex flex-wrap align-items-center gap-4 mb-4 pb-4 border-bottom border-light"> <span class="tag-urgencia <?php echo $urgencia_class; ?>"><?php echo htmlspecialchars($pedido['urgencia']); ?></span>
                <span><i data-lucide="tag"></i> <?php echo htmlspecialchars($pedido['categoria']); ?></span>
                <span><i data-lucide="calendar"></i> Postado em <?php echo date('d/m/Y', strtotime($pedido['data_postagem'])); ?></span>
                <?php if ($tem_localizacao): ?>
                    <span><i data-lucide="map-pin"></i> <?php echo htmlspecialchars($pedido['cidade']) . ' - ' . htmlspecialchars($pedido['estado']); ?></span>
                <?php endif; ?>
            </div>

            <div class="detalhe-descricao mb-5">
                <h5 class="mb-3">Detalhes do Pedido:</h5>
                <p><?php echo nl2br(htmlspecialchars($pedido['descricao'])); ?></p>
            </div>

            <div class="detalhe-footer mt-4 mb-5 text-center">
                <?php if ($e_dono): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/caixa_entrada.php" class="btn btn-primary btn-lg ajudar-btn">
                        <i data-lucide="message-square"></i> Ver Conversas Deste Pedido
                    </a>
                <?php elseif (isset($_SESSION['usuario_id'])): ?>
                    <form action="<?php echo BASE_URL; ?>/includes/iniciar_chat.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                        <button type="submit" class="btn btn-success btn-lg ajudar-btn">
                            <i data-lucide="message-circle"></i> Iniciar Conversa com <?php echo htmlspecialchars(explode(' ', $pedido['autor_nome'])[0]); // Mostra só o primeiro nome ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/pages/login.php" class="btn btn-success btn-lg ajudar-btn">
                        <i data-lucide="log-in"></i> Faça login para conversar com <?php echo htmlspecialchars(explode(' ', $pedido['autor_nome'])[0]); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="comentarios-section pt-4"> <h3 class="mb-4 text-center">Discussão Pública (<?php echo count($comentarios); ?>)</h3>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="card p-3 mb-4 bg-light border-0">
                    <form action="../includes/processa_comentario.php" method="POST" class="form-comentario">
                        <input type='hidden' name='csrf_token' value='<?php echo $_SESSION['csrf_token']; ?>'>
                        <input type='hidden' name='pedido_id' value='<?php echo $pedido['id']; ?>'>
                        <div class="mb-3">
                            <textarea class="form-control" name="comentario" rows="3" placeholder="Deixe sua pergunta ou mensagem de apoio pública..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success"><i data-lucide="send"></i> Enviar Comentário</button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-light text-center">
                    <a href="<?php echo BASE_URL; ?>/pages/login.php">Faça login</a> para participar da discussão pública.
                </div>
                <?php endif; ?>

                <?php if (empty($comentarios_arvore)): ?>
                    <p class="text-secondary text-center">Nenhum comentário público ainda. Seja o primeiro a interagir!</p>
                <?php else: ?>
                    <?php renderizar_comentarios($comentarios_arvore); ?>
                <?php endif; ?>
            </div> </div> <?php endif; ?>
</main>

<?php
if ($conn) { pg_close($conn); }
require_once '../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ... (código JS para botões Responder/Cancelar) ...
    document.querySelectorAll('.resposta-form').forEach(form => form.style.display = 'none');
    document.querySelectorAll('.btn-responder').forEach(button => { button.addEventListener('click', function() { const commentId = this.getAttribute('data-comment-id'); const form = document.getElementById('form-resposta-' + commentId); if (form) { document.querySelectorAll('.resposta-form').forEach(f => f.style.display = 'none'); form.style.display = 'block'; form.querySelector('textarea').focus(); } }); });
    document.querySelectorAll('.btn-cancelar-resposta').forEach(button => { button.addEventListener('click', function() { const commentId = this.getAttribute('data-comment-id'); const form = document.getElementById('form-resposta-' + commentId); if (form) { form.style.display = 'none'; } }); });
});
</script>