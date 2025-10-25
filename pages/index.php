<?php
$page_title = 'AjudaJá - Home';
require_once '../includes/config.php'; // Usa require_once
require_once '../includes/session.php'; // Usa require_once

// Busca categorias distintas dos pedidos ABERTOS
$categorias = [];
$result_cat = pg_query($conn, "SELECT DISTINCT categoria FROM pedidos WHERE status='Aberto' ORDER BY categoria");
if ($result_cat) { while ($row = pg_fetch_assoc($result_cat)) { $categorias[] = $row['categoria']; } }

// Lista de Estados Brasileiros para o dropdown
$estados_brasileiros = [
    'AC'=>'Acre', 'AL'=>'Alagoas', 'AP'=>'Amapá', 'AM'=>'Amazonas', 'BA'=>'Bahia', 'CE'=>'Ceará', 'DF'=>'Distrito Federal', 'ES'=>'Espírito Santo',
    'GO'=>'Goiás', 'MA'=>'Maranhão', 'MT'=>'Mato Grosso', 'MS'=>'Mato Grosso do Sul', 'MG'=>'Minas Gerais', 'PA'=>'Pará', 'PB'=>'Paraíba',
    'PR'=>'Paraná', 'PE'=>'Pernambuco', 'PI'=>'Piauí', 'RJ'=>'Rio de Janeiro', 'RN'=>'Rio Grande do Norte', 'RS'=>'Rio Grande do Sul',
    'RO'=>'Rondônia', 'RR'=>'Roraima', 'SC'=>'Santa Catarina', 'SP'=>'São Paulo', 'SE'=>'Sergipe', 'TO'=>'Tocantins'
];

// Recupera valores dos filtros atuais da URL
$busca_get = trim($_GET['busca'] ?? '');
$categoria_get = trim($_GET['categoria'] ?? '');
$urgencia_get = trim($_GET['urgencia'] ?? '');
$estado_get = trim($_GET['estado'] ?? '');
$cidade_get = trim($_GET['cidade'] ?? '');

require_once '../includes/header.php'; // Usa require_once
?>

<section class="hero-section text-center text-white py-5">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Ajuda Comunitária ao seu Alcance</h1>
        <p class="lead mb-4">Encontre ou ofereça ajuda de forma rápida e segura na sua comunidade.</p>
         <?php if (isset($_SESSION['usuario_id'])): ?>
             <a href="<?php echo BASE_URL; ?>/pages/cadastrar.php" class="btn btn-light btn-lg"><i data-lucide="plus-circle"></i> Criar Novo Pedido</a>
         <?php else: ?>
             <a href="<?php echo BASE_URL; ?>/pages/registrar.php" class="btn btn-light btn-lg">Cadastre-se para Ajudar</a>
         <?php endif; ?>
    </div>
</section>
<main class="container my-5">

    <form action="index.php" method="GET" class="row mb-5 g-3 align-items-stretch filter-form p-4 needs-validation">

        <div class="col-12 mb-3 text-center">
            <h5 class="filter-title"><i data-lucide="filter"></i> Encontre um Pedido</h5>
        </div>

        <div class="col-lg-6 col-md-12">
            <label for="busca" class="form-label">Buscar por termo</label>
            <input type="text" class="form-control form-control-lg" id="busca" name="busca" placeholder="O que você procura?" value="<?php echo htmlspecialchars($busca_get); ?>">
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <label for="filtro-categoria" class="form-label">Categoria</label>
            <select class="form-select form-select-lg" id="filtro-categoria" name="categoria">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($categoria_get == $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <label for="urgencia" class="form-label">Urgência</label>
            <select class="form-select form-select-lg" id="urgencia" name="urgencia">
                <option value="">Todas</option>
                <option value="Urgente" <?php echo ($urgencia_get == 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                <option value="Pode Esperar" <?php echo ($urgencia_get == 'Pode Esperar') ? 'selected' : ''; ?>>Pode Esperar</option>
                <option value="Daqui a uma Semana" <?php echo ($urgencia_get == 'Daqui a uma Semana') ? 'selected' : ''; ?>>Daqui a uma Semana</option>
            </select>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
             <label for="estado" class="form-label">Estado (UF)</label>
             <select class="form-select form-select-lg" id="estado" name="estado">
                 <option value="">Brasil</option>
                 <?php foreach ($estados_brasileiros as $uf => $nome): ?>
                     <option value="<?php echo $uf; ?>" <?php echo ($estado_get == $uf) ? 'selected' : ''; ?>>
                         <?php echo $uf; ?>
                     </option>
                 <?php endforeach; ?>
             </select>
        </div>
        <div class="col-lg-7 col-md-8 col-sm-6">
            <label for="cidade" class="form-label">Cidade</label>
            <input type="text" class="form-control form-control-lg" id="cidade" name="cidade" value="<?php echo htmlspecialchars($cidade_get); ?>" placeholder="Digite o nome da cidade">
        </div>
        <div class="col-lg-2 col-md-12 col-sm-12 d-flex align-items-end"> <button type="submit" class="btn btn-success btn-lg w-100 filter-btn">
                <i data-lucide="search"></i> Buscar
            </button>
        </div>
    </form>

    <div id="lista-pedidos" class="row">
        <?php require_once '../includes/lista_pedidos.php'; // Usa require_once ?>
    </div>
</main>

<?php
// Garante fechamento da conexão antes do footer
if ($conn) { pg_close($conn); }
require_once '../includes/footer.php'; // Usa require_once
?>