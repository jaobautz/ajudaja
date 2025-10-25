<?php
$page_title = 'Minhas Conversas';
require_once '../includes/config.php'; // Usa require_once
require_once '../includes/autenticacao.php'; // Usa require_once
require_once '../includes/session.php'; // Usa require_once

$usuario_id = $_SESSION['usuario_id'];

// === REPUTAÇÃO: Query atualizada para buscar status_conversa ===
$sql = "
    SELECT 
        c.id as conversa_id,
        c.status_conversa, -- Adicionado status
        c.usuario_criador_id, -- Adicionado ID do criador
        c.usuario_voluntario_id, -- Adicionado ID do voluntário
        p.titulo as pedido_titulo,
        p.id as pedido_id,
        CASE
            WHEN c.usuario_criador_id = $1 THEN u_vol.id
            ELSE u_criador.id
        END as outro_usuario_id,
        CASE
            WHEN c.usuario_criador_id = $1 THEN u_vol.nome
            ELSE u_criador.nome
        END as outro_usuario_nome
    FROM 
        conversas c
    JOIN 
        pedidos p ON c.pedido_id = p.id
    JOIN 
        usuarios u_criador ON c.usuario_criador_id = u_criador.id
    JOIN 
        usuarios u_vol ON c.usuario_voluntario_id = u_vol.id
    WHERE 
        c.usuario_criador_id = $1 OR c.usuario_voluntario_id = $1
    ORDER BY 
        c.data_criacao DESC
";

if (!@pg_prepare($conn, "get_caixa_entrada", $sql)) { die("Erro ao preparar consulta da caixa de entrada."); }
$result = pg_execute($conn, "get_caixa_entrada", array($usuario_id));

$conversas = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $conversas[] = $row;
    }
}

// Inclui o header APÓS buscar os dados
require_once '../includes/header.php'; // Usa require_once
?>

<main class="container my-5">
    <div class="text-center mb-5">
        <h1 class="display-6 fw-bold">Minhas Conversas</h1>
        <p class="lead text-secondary">Acompanhe aqui suas conversas sobre os pedidos.</p>
    </div>

    <?php if (isset($_SESSION['sucesso'])) { echo "<div class='alert alert-success'>".$_SESSION['sucesso']."</div>"; unset($_SESSION['sucesso']); } ?>
    <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>".$_SESSION['erro']."</div>"; unset($_SESSION['erro']); } ?>

    <div class="card p-4" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
        <div class="list-group list-group-flush">
            <?php if (empty($conversas)): ?>
                <p class="text-center text-secondary p-5">Você ainda não possui nenhuma conversa.</p>
            <?php else: ?>
                <?php foreach ($conversas as $conversa): 
                    $avatar_seed = urlencode($conversa['outro_usuario_nome']);
                    // Verifica se o usuário logado é o CRIADOR do pedido desta conversa
                    $sou_criador = ($conversa['usuario_criador_id'] == $usuario_id);
                    $ajuda_confirmada = ($conversa['status_conversa'] == 'Ajuda Confirmada');
                ?>
                    <div class="list-group-item px-3 py-3">
                        <div class="d-flex w-100 align-items-center justify-content-between">
                            <a href="chat.php?conversa_id=<?php echo $conversa['conversa_id']; ?>" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1">
                                <img src="https://api.dicebear.com/8.x/initials/svg?seed=<?php echo $avatar_seed; ?>" alt="Avatar" class="autor-avatar me-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">
                                        <?php echo htmlspecialchars($conversa['outro_usuario_nome']); ?>
                                        <?php if ($ajuda_confirmada): ?>
                                            <span class="badge bg-success ms-2" style="font-size: 0.7em;"><i data-lucide="check-circle" style="width: 1em; height: 1em;"></i> Ajuda Confirmada</span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="mb-0 text-secondary" style="font-size: 0.9rem;">Sobre o pedido: "<?php echo htmlspecialchars($conversa['pedido_titulo']); ?>"</p>
                                </div>
                            </a>

                            <div class="ms-3 text-end" style="min-width: 150px;"> <?php if ($sou_criador && !$ajuda_confirmada): ?>
                                    <form action="../includes/marcar_ajuda.php" method="POST" onsubmit="return confirm('Tem certeza que deseja confirmar que recebeu ajuda nesta conversa? Isso dará +1 ponto de reputação ao voluntário.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="conversa_id" value="<?php echo $conversa['conversa_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i data-lucide="award" style="width: 1em; height: 1em;"></i> Confirmar Ajuda
                                        </button>
                                    </form>
                                <?php elseif ($sou_criador && $ajuda_confirmada): ?>
                                    <span class="text-success" style="font-size: 0.8rem;"><i data-lucide="check-circle" style="width: 1em; height: 1em;"></i> Agradecimento enviado</span>
                                <?php else: ?>
                                    <a href="chat.php?conversa_id=<?php echo $conversa['conversa_id']; ?>" class="btn btn-sm btn-outline-primary">
                                         <i data-lucide="message-circle" style="width: 1em; height: 1em;"></i> Abrir Chat
                                     </a>
                                <?php endif; ?>
                            </div>
                            </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
if ($conn) { pg_close($conn); }
require_once '../includes/footer.php'; // Usa require_once
?>