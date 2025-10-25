<?php
$page_title = 'Chat';
include '../includes/autenticacao.php';
require_once '../includes/config.php';

$conversa_id = filter_input(INPUT_GET, 'conversa_id', FILTER_VALIDATE_INT);
$usuario_id = $_SESSION['usuario_id'];

if (!$conversa_id) {
    $_SESSION['erro'] = "Conversa não encontrada.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

// 1. Verificar permissão de acesso ao chat
$sql_check = "
    SELECT 
        c.id, p.titulo as pedido_titulo,
        u_criador.nome as criador_nome,
        u_vol.nome as voluntario_nome
    FROM conversas c
    JOIN pedidos p ON c.pedido_id = p.id
    JOIN usuarios u_criador ON c.usuario_criador_id = u_criador.id
    JOIN usuarios u_vol ON c.usuario_voluntario_id = u_vol.id
    WHERE c.id = $1 AND (c.usuario_criador_id = $2 OR c.usuario_voluntario_id = $3)
";
pg_prepare($conn, "get_conversa_details", $sql_check);
$result_check = pg_execute($conn, "get_conversa_details", array($conversa_id, $usuario_id, $usuario_id));

if (!$result_check || pg_num_rows($result_check) == 0) {
    $_SESSION['erro'] = "Você não tem permissão para acessar este chat.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}
$conversa = pg_fetch_assoc($result_check);
$outro_usuario_nome = ($conversa['criador_nome'] == $_SESSION['usuario_nome']) ? $conversa['voluntario_nome'] : $conversa['criador_nome'];
$page_title = "Chat com " . htmlspecialchars($outro_usuario_nome);

// 2. Buscar todas as mensagens
$sql_msgs = "
    SELECT m.*, r.nome as remetente_nome 
    FROM mensagens m 
    JOIN usuarios r ON m.remetente_id = r.id 
    WHERE m.conversa_id = $1 
    ORDER BY m.data_envio ASC
";
pg_prepare($conn, "get_mensagens", $sql_msgs);
$result_msgs = pg_execute($conn, "get_mensagens", array($conversa_id));
$mensagens = [];
if ($result_msgs) {
    while ($row = pg_fetch_assoc($result_msgs)) {
        $mensagens[] = $row;
    }
}

// 3. Incluir o header DEPOIS de definir o $page_title
include '../includes/header.php';
?>

<main class="container my-5">
    <div class="chat-container mx-auto" style="max-width: 800px;">
        <div class="card" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
            <div class="card-header bg-white p-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($outro_usuario_nome); ?></h5>
                    <p class="mb-0 text-secondary" style="font-size: 0.9rem;">
                        Sobre: <?php echo htmlspecialchars($conversa['pedido_titulo']); ?>
                    </p>
                </div>
                <a href="<?php echo BASE_URL; ?>/pages/caixa_entrada.php" class="btn btn-sm btn-outline-secondary"><i data-lucide="arrow-left"></i> Voltar</a>
            </div>

            <div class="card-body" style="height: 500px; overflow-y: auto; background-color: var(--background-color);" id="chat-body">
                <?php if (empty($mensagens)): ?>
                    <p class="text-center text-secondary mt-3">Este é o início da sua conversa.</p>
                <?php else: ?>
                    <?php foreach ($mensagens as $msg): 
                        $e_minha = ($msg['remetente_id'] == $usuario_id);
                        $avatar_seed = urlencode($msg['remetente_nome']);
                    ?>
                        <div class="d-flex mb-3 <?php echo $e_minha ? 'justify-content-end' : 'justify-content-start'; ?>">
                            <?php if (!$e_minha): ?>
                                <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avatar_seed; ?>" alt="A" class="autor-avatar me-2" style="width: 30px; height: 30px;">
                            <?php endif; ?>
                            
                            <div class="p-3 <?php echo $e_minha ? 'bg-success text-white' : 'bg-light text-dark border'; ?>" style="border-radius: 1rem; max-width: 70%;">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($msg['mensagem'])); ?></p>
                                <span class="d-block text-end mt-1" style="font-size: 0.75rem; <?php echo $e_minha ? 'opacity: 0.7' : 'color: var(--text-secondary);'; ?>">
                                    <?php echo date('H:i', strtotime($msg['data_envio'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-white p-3 border-top">
                <form action="../includes/enviar_mensagem.php" method="POST" class="d-flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="conversa_id" value="<?php echo $conversa_id; ?>">
                    <textarea class="form-control" name="mensagem" rows="2" placeholder="Digite sua mensagem..." style="resize: none;" required></textarea>
                    <button type="submit" class="btn btn-success"><i data-lucide="send"></i></button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    // Script para rolar o chat para a última mensagem
    document.addEventListener('DOMContentLoaded', function() {
        const chatBody = document.getElementById('chat-body');
        if(chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    });
</script>

<?php
if ($conn) { pg_close($conn); }
include '../includes/footer.php';
?>