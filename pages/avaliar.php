<?php
$page_title = 'Avaliar Ajuda';
require_once '../includes/config.php'; 
require_once '../includes/autenticacao.php'; 
require_once '../includes/session.php'; 

$conversa_id = filter_input(INPUT_GET, 'conversa_id', FILTER_VALIDATE_INT);
$usuario_id = $_SESSION['usuario_id'];

if (!$conversa_id) {
    $_SESSION['erro'] = "Conversa inválida.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

// Busca detalhes da conversa para exibir e para validação
$sql = "
    SELECT 
        c.id, c.status_conversa, c.usuario_criador_id, c.usuario_voluntario_id,
        p.titulo as pedido_titulo,
        u_vol.nome as voluntario_nome
    FROM conversas c
    JOIN pedidos p ON c.pedido_id = p.id
    JOIN usuarios u_vol ON c.usuario_voluntario_id = u_vol.id
    WHERE c.id = $1 AND c.usuario_criador_id = $2
";
if (!@pg_prepare($conn, "get_conversa_para_avaliar", $sql)) { die("Erro ao preparar consulta."); }
$result = pg_execute($conn, "get_conversa_para_avaliar", array($conversa_id, $usuario_id));

if (!$result || pg_num_rows($result) == 0) {
    $_SESSION['erro'] = "Você não pode avaliar esta conversa ou ela não existe.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

$conversa = pg_fetch_assoc($result);

// Verifica se já foi avaliada
if ($conversa['status_conversa'] == 'Ajuda Confirmada') {
     $_SESSION['erro'] = "Esta ajuda já foi avaliada anteriormente.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

require_once '../includes/header.php'; 
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="display-6 fw-bold">Avaliar Voluntário</h1>
                <p class="lead text-secondary">
                    Você está avaliando <strong><?php echo htmlspecialchars($conversa['voluntario_nome']); ?></strong> pela ajuda no pedido:
                    <br>"<?php echo htmlspecialchars($conversa['pedido_titulo']); ?>"
                </p>
            </div>
            
            <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>".$_SESSION['erro']."</div>"; unset($_SESSION['erro']); } ?>

            <div class="card p-4 p-md-5" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                
                <form action="../includes/processa_avaliacao.php" method="POST" id="form-avaliacao" class="row g-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="conversa_id" value="<?php echo $conversa['id']; ?>">
                    <input type="hidden" name="avaliado_id" value="<?php echo $conversa['usuario_voluntario_id']; ?>">
                    
                    <div class="col-12">
                        <label class="form-label fs-5">Sua nota (de 1 a 5 estrelas):</label>
                        <div class="rating-stars mb-3">
                            <input type="radio" name="nota" id="star5" value="5" required><label for="star5" title="5 estrelas"><i data-lucide="star"></i></label>
                            <input type="radio" name="nota" id="star4" value="4"><label for="star4" title="4 estrelas"><i data-lucide="star"></i></label>
                            <input type="radio" name="nota" id="star3" value="3"><label for="star3" title="3 estrelas"><i data-lucide="star"></i></label>
                            <input type="radio" name="nota" id="star2" value="2"><label for="star2" title="2 estrelas"><i data-lucide="star"></i></label>
                            <input type="radio" name="nota" id="star1" value="1"><label for="star1" title="1 estrela"><i data-lucide="star"></i></label>
                        </div>
                    </div>

                    <div class="col-12 form-floating">
                        <textarea class="form-control" id="comentario_avaliacao" name="comentario_avaliacao" placeholder="Deixe um comentário (opcional)" style="height: 120px;"></textarea>
                        <label for="comentario_avaliacao">Deixe um comentário (opcional)</label>
                        <div class="form-text">Seu comentário será público no perfil do voluntário.</div>
                    </div>
                    
                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-success w-100 py-3"><i data-lucide="send"></i> Enviar Avaliação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php
if ($conn) { pg_close($conn); }
require_once '../includes/footer.php'; 
?>