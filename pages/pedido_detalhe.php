<?php
$page_title = 'Detalhes do Pedido - AjudaJá';
include '../includes/config.php';
include '../includes/header.php';

$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$erro = null;
$pedido = null;
$comentarios = [];

if (!$pedido_id) {
    $erro = "Pedido não encontrado ou ID inválido.";
} else {
    // Busca os detalhes do pedido
    $sql_pedido = "SELECT p.*, u.nome as autor_nome FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = $1";
    if (@pg_query($conn, "DEALLOCATE get_pedido_detalhe")) {}
    pg_prepare($conn, "get_pedido_detalhe", $sql_pedido);
    $result_pedido = pg_execute($conn, "get_pedido_detalhe", array($pedido_id));

    if ($result_pedido && pg_num_rows($result_pedido) === 1) {
        $pedido = pg_fetch_assoc($result_pedido);

        // Busca os comentários para este pedido
        $sql_comentarios = "SELECT c.*, u.nome as autor_nome FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.pedido_id = $1 ORDER BY c.data_comentario ASC";
        if (@pg_query($conn, "DEALLOCATE get_comentarios")) {}
        pg_prepare($conn, "get_comentarios", $sql_comentarios);
        $result_comentarios = pg_execute($conn, "get_comentarios", array($pedido_id));
        if ($result_comentarios) {
            while ($row = pg_fetch_assoc($result_comentarios)) {
                $comentarios[] = $row;
            }
        }
    } else {
        $erro = "Pedido não encontrado ou indisponível.";
    }
}
?>

<main class="container my-5">
    <?php if (isset($_SESSION['sucesso'])) { echo "<div class='alert alert-success'>".$_SESSION['sucesso']."</div>"; unset($_SESSION['sucesso']); } ?>
    <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>".$_SESSION['erro']."</div>"; unset($_SESSION['erro']); } ?>

    <?php if ($erro): ?>
        <div class="alert alert-danger text-center"><?php echo $erro; ?></div>
    <?php elseif ($pedido): 
        $avatar_seed = urlencode($pedido['autor_nome']);
        $urgencia_class = strtolower(str_replace([' ', 'ã'], ['-', 'a'], $pedido['urgencia']));
    ?>
        <div class="detalhe-container">
            <div class="detalhe-header">
                <div class="pedido-autor mb-3">
                    <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avatar_seed; ?>" alt="Avatar" class="autor-avatar">
                    <span class="autor-nome"><?php echo htmlspecialchars($pedido['autor_nome']); ?></span>
                </div>
                <h1><?php echo htmlspecialchars($pedido['titulo']); ?></h1>
                <div class="detalhe-meta d-flex flex-wrap align-items-center gap-4 mb-4">
                    <span class="tag-urgencia <?php echo $urgencia_class; ?>"><?php echo htmlspecialchars($pedido['urgencia']); ?></span>
                    <span><i data-lucide="tag"></i> <?php echo htmlspecialchars($pedido['categoria']); ?></span>
                    <span><i data-lucide="calendar"></i> Postado em <?php echo date('d/m/Y', strtotime($pedido['data_postagem'])); ?></span>
                </div>
            </div>
            
            <hr class="my-4">

            <div class="detalhe-descricao">
                <p><?php echo nl2br(htmlspecialchars($pedido['descricao'])); ?></p>
            </div>

            <div class="detalhe-footer mt-5">
                <a href="https://wa.me/55<?php echo htmlspecialchars($pedido['whatsapp_numero']); ?>?text=Oi!%20Vi%20seu%20pedido%20no%20AjudaJá:%20'<?php echo urlencode($pedido['titulo']); ?>'.%20Como%20posso%20ajudar?" target="_blank" class="btn btn-success btn-lg ajudar-btn">
                    <i data-lucide="message-circle"></i> Ajudar via WhatsApp
                </a>
            </div>

            <div class="comentarios-section">
                <h3 class="mb-4">Discussão (<?php echo count($comentarios); ?>)</h3>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="card p-3 mb-4 bg-light border-0">
                    <form action="../includes/processa_comentario.php" method="POST" class="form-comentario">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                        <div class="mb-3">
                            <textarea class="form-control" name="comentario" rows="3" placeholder="Deixe sua pergunta ou mensagem de apoio..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success"><i data-lucide="send"></i> Enviar</button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-light text-center">
                    <a href="login.php">Faça login</a> para participar da discussão.
                </div>
                <?php endif; ?>

                <?php if (empty($comentarios)): ?>
                    <p class="text-secondary text-center">Nenhum comentário ainda. Seja o primeiro a interagir!</p>
                <?php else: ?>
                    <?php foreach ($comentarios as $comentario): 
                        $comentario_avatar_seed = urlencode($comentario['autor_nome']);
                    ?>
                    <div class="comentario-card">
                        <div class="comentario-avatar">
                            <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $comentario_avatar_seed; ?>" alt="Avatar">
                        </div>
                        <div class="comentario-body">
                            <div class="comentario-header">
                                <span class="comentario-autor"><?php echo htmlspecialchars($comentario['autor_nome']); ?></span>
                                <span class="comentario-data"><?php echo date('d/m/Y H:i', strtotime($comentario['data_comentario'])); ?></span>
                            </div>
                            <p class="comentario-texto"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php 
if ($conn) { pg_close($conn); }
include '../includes/footer.php'; 
?>