<?php
$page_title = 'Cadastrar Pedido - AjudaJá';
require_once '../includes/config.php';       
require_once '../includes/autenticacao.php'; 
require_once '../includes/session.php';      

// Recuperar erros e dados antigos
$erros = $_SESSION['erro_campos'] ?? [];
$old_data = $_SESSION['old_data'] ?? [];
unset($_SESSION['erro_campos'], $_SESSION['old_data']);

// Funções auxiliares
function exibir_erro($campo, $erros) { if (isset($erros[$campo])) { echo "<div class='invalid-feedback d-block'>{$erros[$campo]}</div>"; } }
function old($campo, $old_data) { return htmlspecialchars($old_data[$campo] ?? ''); }
function old_select($campo, $valor_opcao, $old_data) { return (isset($old_data[$campo]) && $old_data[$campo] == $valor_opcao) ? 'selected' : ''; }

require_once '../includes/header.php'; 
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="display-6 fw-bold">Novo Pedido de Ajuda</h1>
                <p class="lead text-secondary">Descreva sua necessidade para que a comunidade possa ajudar.</p>
            </div>
            <?php if (isset($_SESSION['erro'])) { echo "<div class='alert alert-danger'>" . $_SESSION['erro'] . "</div>"; unset($_SESSION['erro']); } ?>
            <div class="card p-4 p-md-5" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                <form action="../includes/salvar_pedido.php" method="POST" id="form-cadastro" class="row g-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="col-md-12 form-floating">
                        <input type="text" class="form-control <?php echo isset($erros['titulo']) ? 'is-invalid' : ''; ?>" id="titulo" name="titulo" value="<?php echo old('titulo', $old_data); ?>" placeholder="Título do Pedido" required>
                        <label for="titulo">Título do Pedido (mínimo 5 caracteres)</label>
                        <?php exibir_erro('titulo', $erros); ?>
                    </div>
                    <div class="col-md-12 form-floating">
                        <textarea class="form-control <?php echo isset($erros['descricao']) ? 'is-invalid' : ''; ?>" id="descricao" name="descricao" placeholder="Descrição Detalhada" style="height: 150px;" required><?php echo old('descricao', $old_data); ?></textarea>
                        <label for="descricao">Descrição Detalhada (mínimo 20 caracteres)</label>
                        <?php exibir_erro('descricao', $erros); ?>
                    </div>
                    <div class="col-md-6 form-floating">
                        <select class="form-select <?php echo isset($erros['urgencia']) ? 'is-invalid' : ''; ?>" id="urgencia" name="urgencia" required>
                            <option value="" disabled <?php echo empty(old('urgencia', $old_data)) ? 'selected' : ''; ?>>Selecione uma opção</option>
                            <option value="Urgente" <?php echo old_select('urgencia', 'Urgente', $old_data); ?>>Urgente</option>
                            <option value="Pode Esperar" <?php echo old_select('urgencia', 'Pode Esperar', $old_data); ?>>Pode Esperar</option>
                            <option value="Daqui a uma Semana" <?php echo old_select('urgencia', 'Daqui a uma Semana', $old_data); ?>>Daqui a uma Semana</option>
                        </select>
                        <label for="urgencia">Nível de Urgência</label>
                        <?php exibir_erro('urgencia', $erros); ?>
                    </div>
                    <div class="col-md-6 form-floating">
                        <select class="form-select <?php echo isset($erros['categoria']) ? 'is-invalid' : ''; ?>" id="categoria" name="categoria" required>
                             <option value="" disabled <?php echo empty(old('categoria', $old_data)) ? 'selected' : ''; ?>>Selecione uma categoria</option>
                            <option value="Cesta Básica" <?php echo old_select('categoria', 'Cesta Básica', $old_data); ?>>Cesta Básica</option>
                            <option value="Carona" <?php echo old_select('categoria', 'Carona', $old_data); ?>>Carona</option>
                            <option value="Apoio Emocional" <?php echo old_select('categoria', 'Apoio Emocional', $old_data); ?>>Apoio Emocional</option>
                            <option value="Doação de Itens" <?php echo old_select('categoria', 'Doação de Itens', $old_data); ?>>Doação de Itens</option>
                            <option value="Serviços Voluntários" <?php echo old_select('categoria', 'Serviços Voluntários', $old_data); ?>>Serviços Voluntários</option>
                            <option value="Outros" <?php echo old_select('categoria', 'Outros', $old_data); ?>>Outros</option>
                        </select>
                        <label for="categoria">Categoria</label>
                        <?php exibir_erro('categoria', $erros); ?>
                    </div>
                    <div class="col-md-6 form-floating">
                        <input type="text" class="form-control <?php echo isset($erros['cep']) ? 'is-invalid' : ''; ?>" id="cep" name="cep" value="<?php echo old('cep', $old_data); ?>" placeholder="CEP (Opcional)" pattern="\d{5}-?\d{3}">
                        <label for="cep">CEP (Opcional)</label>
                        <div class="form-text">Informe o CEP para ajudar na localização (Ex: 01001-000).</div>
                        <?php exibir_erro('cep', $erros); ?>
                    </div>
                    <div class="col-md-6 form-floating"> 
                        <input type="tel" class="form-control <?php echo isset($erros['whatsapp']) ? 'is-invalid' : ''; ?>" id="whatsapp" name="whatsapp" value="<?php echo old('whatsapp', $old_data); ?>" placeholder="Seu Número de WhatsApp" pattern="\d{10,11}" required>
                        <label for="whatsapp">Seu Número de WhatsApp (10 ou 11 dígitos)</label>
                        <?php exibir_erro('whatsapp', $erros); ?>
                    </div>
                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-success w-100 py-3"><i data-lucide="send"></i> Postar Meu Pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>