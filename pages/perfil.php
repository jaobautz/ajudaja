<?php
$page_title = 'Meu Perfil';
include '../includes/autenticacao.php'; // Protege a página
require_once '../includes/config.php';
include '../includes/header.php';

// Busca os dados atuais do usuário para preencher o formulário
$usuario_id = $_SESSION['usuario_id'];
$sql_user = "SELECT nome, email, telefone FROM usuarios WHERE id = $1";
pg_prepare($conn, "get_user_perfil", $sql_user);
$result_user = pg_execute($conn, "get_user_perfil", array($usuario_id));
$usuario = pg_fetch_assoc($result_user);
if (!$usuario) {
    // Se não encontrar o usuário (raro), desloga por segurança
    header('Location: ' . BASE_URL . '/includes/logout.php');
    exit;
}

// Recupera erros de validação e dados antigos (Formulário de Perfil)
$erros_perfil = $_SESSION['erro_campos'] ?? [];
$old_data_perfil = $_SESSION['old_data'] ?? [];
unset($_SESSION['erro_campos'], $_SESSION['old_data']);

// Recupera erros de validação (Formulário de Senha)
$erros_senha = $_SESSION['erro_campos_senha'] ?? [];
unset($_SESSION['erro_campos_senha']);

// Funções auxiliares
function exibir_erro($campo, $erros) {
    if (isset($erros[$campo])) {
        echo "<div class='invalid-feedback d-block'>{$erros[$campo]}</div>";
    }
}
function old_or_default($campo, $old_data, $default_value) {
    return htmlspecialchars($old_data[$campo] ?? $default_value);
}
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5">
                <h1 class="display-6 fw-bold">Meu Perfil</h1>
                <p class="lead text-secondary">Atualize seus dados pessoais e de segurança.</p>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card p-4 p-md-5 h-100" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                        <h4>Dados Pessoais</h4>
                        <hr class="my-3">
                        <?php if (isset($_SESSION['sucesso'])) { echo "<div class='alert alert-success'>".$_SESSION['sucesso']."</div>"; unset($_SESSION['sucesso']); } ?>
                        <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>".$_SESSION['erro']."</div>"; unset($_SESSION['erro']); } ?>
                        
                        <form action="../includes/processa_perfil.php" method="POST" class="row g-3">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="col-12">
                                <label for="perfil_nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control <?php echo isset($erros_perfil['perfil_nome']) ? 'is-invalid' : ''; ?>" id="perfil_nome" name="nome" value="<?php echo old_or_default('nome', $old_data_perfil, $usuario['nome']); ?>" required>
                                <?php exibir_erro('perfil_nome', $erros_perfil); ?>
                            </div>
                            <div class="col-12">
                                <label for="perfil_email" class="form-label">Email</label>
                                <input type="email" class="form-control <?php echo isset($erros_perfil['perfil_email']) ? 'is-invalid' : ''; ?>" id="perfil_email" name="email" value="<?php echo old_or_default('email', $old_data_perfil, $usuario['email']); ?>" required>
                                <?php exibir_erro('perfil_email', $erros_perfil); ?>
                            </div>
                            <div class="col-12">
                                <label for="perfil_telefone" class="form-label">Telefone (com DDD)</label>
                                <input type="tel" class="form-control <?php echo isset($erros_perfil['perfil_telefone']) ? 'is-invalid' : ''; ?>" id="perfil_telefone" name="telefone" value="<?php echo old_or_default('telefone', $old_data_perfil, $usuario['telefone']); ?>" required>
                                <?php exibir_erro('perfil_telefone', $erros_perfil); ?>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success w-100"><i data-lucide="save"></i> Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card p-4 p-md-5 h-100" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                        <h4>Alterar Senha</h4>
                        <hr class="my-3">
                        <?php if (isset($_SESSION['sucesso_senha'])) { echo "<div class='alert alert-success'>".$_SESSION['sucesso_senha']."</div>"; unset($_SESSION['sucesso_senha']); } ?>
                        <?php if (isset($_SESSION['erro_senha'])) { echo "<div class='alert alert-danger'>".$_SESSION['erro_senha']."</div>"; unset($_SESSION['erro_senha']); } ?>
                        
                        <form action="../includes/processa_senha.php" method="POST" class="row g-3">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="col-12">
                                <label for="senha_atual" class="form-label">Senha Atual</label>
                                <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                            </div>
                            <div class="col-12">
                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control <?php echo isset($erros_senha['nova_senha']) ? 'is-invalid' : ''; ?>" id="nova_senha" name="nova_senha" aria-describedby="senhaHelp" required>
                                <div id="senhaHelp" class="form-text">Mínimo 8 caracteres, com letra e número.</div>
                                <?php exibir_erro('nova_senha', $erros_senha); ?>
                            </div>
                            <div class="col-12">
                                <label for="nova_senha_confirm" class="form-label">Confirme a Nova Senha</label>
                                <input type="password" class="form-control <?php echo isset($erros_senha['nova_senha_confirm']) ? 'is-invalid' : ''; ?>" id="nova_senha_confirm" name="nova_senha_confirm" required>
                                <?php exibir_erro('nova_senha_confirm', $erros_senha); ?>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-dark w-100"><i data-lucide="lock"></i> Alterar Senha</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
if ($conn) { pg_close($conn); }
include '../includes/footer.php';
?>