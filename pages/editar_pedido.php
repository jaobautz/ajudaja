<?php
require_once '../includes/config.php';
require_once '../includes/autenticacao.php';
require_once '../includes/session.php';

// Funções de formulário
$erros = $_SESSION['erro_campos'] ?? []; $old_data = $_SESSION['old_data'] ?? []; unset($_SESSION['erro_campos'], $_SESSION['old_data']);
function exibir_erro($campo, $erros) { if (isset($erros[$campo])) { echo "<div class='invalid-feedback d-block'>{$erros[$campo]}</div>"; } }
function old_or_default($campo, $old_data, $default_value) { return htmlspecialchars($old_data[$campo] ?? $default_value ?? ''); }
function old_select_or_default($campo, $valor_opcao, $old_data, $default_value) { $valor_atual = $old_data[$campo] ?? $default_value; return ($valor_atual == $valor_opcao) ? 'selected' : ''; }

// Busca pedido
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); $pedido = null;
if (!$pedido_id) { $_SESSION['erro'] = "ID inválido."; header('Location: ' . BASE_URL . '/pages/dashboard.php'); exit; }
$sql = "SELECT * FROM pedidos WHERE id = $1 AND usuario_id = $2";
if (!@pg_prepare($conn, "get_pedido_for_edit", $sql)) { $_SESSION['erro'] = "Erro DB."; header('Location: ' . BASE_URL . '/pages/dashboard.php'); exit; }
$result = pg_execute($conn, "get_pedido_for_edit", array($pedido_id, $_SESSION['usuario_id']));
if (!$result || pg_num_rows($result) !== 1) { $_SESSION['erro'] = "Permissão negada."; header('Location: ' . BASE_URL . '/pages/dashboard.php'); exit; }
$pedido = pg_fetch_assoc($result);

$page_title = 'Editar: ' . htmlspecialchars($pedido['titulo']);
require_once '../includes/header.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="display-6 fw-bold">Editar Pedido de Ajuda</h1>
                <p class="lead text-secondary">Ajuste as informações do seu pedido.</p>
            </div>

            <?php if (isset($_SESSION['sucesso'])) { echo "<div class='alert alert-success'>".$_SESSION['sucesso']."</div>"; unset($_SESSION['sucesso']); } ?>
            <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>".$_SESSION['erro']."</div>"; unset($_SESSION['erro']); } ?>

            <div class="card p-4 p-md-5" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                <form action="../includes/processa_edicao.php" method="POST" id="form-edicao" class="row g-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                    
                    <div class="col-md-12 form-floating">
                        <input type="text" class="form-control <?php echo isset($erros['titulo']) ? 'is-invalid' : ''; ?>" id="titulo" name="titulo" required maxlength="255" value="<?php echo old_or_default('titulo', $old_data, $pedido['titulo']); ?>" placeholder="Título do Pedido">
                        <label for="titulo">Título do Pedido (mínimo 5 caracteres)</label>
                        <?php exibir_erro('titulo', $erros); ?>
                    </div>
                    <div class="col-md-12 form-floating">
                        <textarea class="form-control <?php echo isset($erros['descricao']) ? 'is-invalid' : ''; ?>" id="descricao" name="descricao" rows="5" required style="height: 150px;" placeholder="Descrição Detalhada"><?php echo old_or_default('descricao', $old_data, $pedido['descricao']); ?></textarea>
                        <label for="descricao">Descrição Detalhada (mínimo 20 caracteres)</label>
                        <?php exibir_erro('descricao', $erros); ?>
                    </div>
                    <div class="col-md-6 form-floating">
                        <select class="form-select <?php echo isset($erros['urgencia']) ? 'is-invalid' : ''; ?>" id="urgencia" name="urgencia" required>
                            <option value="Urgente" <?php echo old_select_or_default('urgencia', 'Urgente', $old_data, $pedido['urgencia']); ?>>Urgente</option>
                            <option value="Pode Esperar" <?php echo old_select_or_default('urgencia', 'Pode Esperar', $old_data, $pedido['urgencia']); ?>>Pode Esperar</option>
                            <option value="Daqui a uma Semana" <?php echo old_select_or_default('urgencia', 'Daqui a uma Semana', $old_data, $pedido['urgencia']); ?>>Daqui a uma Semana</option>
                        </select>
                        <label for="urgencia">Nível de Urgência</label>
                        <?php exibir_erro('urgencia', $erros); ?>
                    </div>
                    <div class="col-md-6 form-floating">
                        <select class="form-select <?php echo isset($erros['categoria']) ? 'is-invalid' : ''; ?>" id="categoria" name="categoria" required>
                            <option value="Cesta Básica" <?php echo old_select_or_default('categoria', 'Cesta Básica', $old_data, $pedido['categoria']); ?>>Cesta Básica</option>
                            <option value="Carona" <?php echo old_select_or_default('categoria', 'Carona', $old_data, $pedido['categoria']); ?>>Carona</option>
                            <option value="Apoio Emocional" <?php echo old_select_or_default('categoria', 'Apoio Emocional', $old_data, $pedido['categoria']); ?>>Apoio Emocional</option>
                            <option value="Doação de Itens" <?php echo old_select_or_default('categoria', 'Doação de Itens', $old_data, $pedido['categoria']); ?>>Doação de Itens</option>
                            <option value="Serviços Voluntários" <?php echo old_select_or_default('categoria', 'Serviços Voluntários', $old_data, $pedido['categoria']); ?>>Serviços Voluntários</option>
                            <option value="Outros" <?php echo old_select_or_default('categoria', 'Outros', $old_data, $pedido['categoria']); ?>>Outros</option>
                        </select>
                        <label for="categoria">Categoria</label>
                        <?php exibir_erro('categoria', $erros); ?>
                    </div>

                    <div class="col-md-12 form-floating">
                        <input type="text" class="form-control <?php echo isset($erros['cep']) ? 'is-invalid' : ''; ?>" id="cep" name="cep" value="<?php echo old_or_default('cep', $old_data, $pedido['cep']); ?>" placeholder="CEP (Opcional)" pattern="\d{5}-?\d{3}">
                        <label for="cep">CEP (Opcional)</label>
                        <div class="form-text">Informe o CEP para ajudar na localização (Ex: 01001-000).</div>
                         <?php exibir_erro('cep', $erros); ?>
                    </div>
                    
                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-success w-100 py-3"><i data-lucide="save"></i> Salvar Alterações</button>
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