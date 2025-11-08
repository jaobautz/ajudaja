<?php 
$page_title = 'AjudaJá - Home';
require_once '../includes/config.php'; 
require_once '../includes/session.php'; 

// Busca categorias (sem alterações)
$categorias = []; $result_cat = pg_query($conn, "SELECT DISTINCT categoria FROM pedidos WHERE status='Aberto' ORDER BY categoria"); if ($result_cat) { while ($row = pg_fetch_assoc($result_cat)) { $categorias[] = $row['categoria']; } }

// Recupera valores dos filtros atuais da URL
$busca_get = trim($_GET['busca'] ?? '');
$categoria_get = trim($_GET['categoria'] ?? '');
$urgencia_get = trim($_GET['urgencia'] ?? '');
$cep_filtro_get = trim($_GET['filtro_cep'] ?? ''); // Novo filtro CEP
$raio_filtro_get = trim($_GET['filtro_raio'] ?? ''); // Novo filtro Raio

require_once '../includes/header.php'; 
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

        <div class="col-lg-7 col-md-12">
            <label for="busca" class="form-label">Buscar por termo</label>
            <input type="text" class="form-control form-control-lg" id="busca" name="busca" placeholder="O que você procura?" value="<?php echo htmlspecialchars($busca_get); ?>">
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <label for="filtro-categoria" class="form-label">Categoria</label>
            <select class="form-select form-select-lg" id="filtro-categoria" name="categoria">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?> <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($categoria_get == $cat) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option> <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-6 col-sm-6">
            <label for="urgencia" class="form-label">Urgência</label>
            <select class="form-select form-select-lg" id="urgencia" name="urgencia">
                <option value="">Todas</option>
                <option value="Urgente" <?php echo ($urgencia_get == 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                <option value="Pode Esperar" <?php echo ($urgencia_get == 'Pode Esperar') ? 'selected' : ''; ?>>Pode Esperar</option>
                <option value="Daqui a uma Semana" <?php echo ($urgencia_get == 'Daqui a uma Semana') ? 'selected' : ''; ?>>Daqui a uma Semana</option>
            </select>
        </div>
        
        <div class="col-lg-5 col-md-5 col-sm-6">
             <label for="filtro_cep" class="form-label">Seu CEP</label>
             <input type="text" class="form-control form-control-lg" id="filtro_cep" name="filtro_cep" value="<?php echo htmlspecialchars($cep_filtro_get); ?>" placeholder="Digite seu CEP">
        </div>
        <div class="col-lg-5 col-md-5 col-sm-6">
            <label for="filtro_raio" class="form-label">Raio de Distância</label>
            <select class="form-select form-select-lg" id="filtro_raio" name="filtro_raio">
                 <option value="">Qualquer Distância</option>
                 <option value="5" <?php echo ($raio_filtro_get == '5') ? 'selected' : ''; ?>>Até 5 km</option>
                 <option value="10" <?php echo ($raio_filtro_get == '10') ? 'selected' : ''; ?>>Até 10 km</option>
                 <option value="25" <?php echo ($raio_filtro_get == '25') ? 'selected' : ''; ?>>Até 25 km</option>
                 <option value="50" <?php echo ($raio_filtro_get == '50') ? 'selected' : ''; ?>>Até 50 km</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 d-flex align-items-end"> <button type="submit" class="btn btn-success btn-lg w-100 filter-btn">
                <i data-lucide="search"></i> Buscar
            </button>
        </div>
    </form>
    
    <div id="lista-pedidos" class="row">
        <?php require_once '../includes/lista_pedidos.php'; ?>
    </div>
</main>

<?php 
if ($conn) { pg_close($conn); } 
require_once '../includes/footer.php'; 
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Inputmask !== 'undefined') {
        const filtroCepInput = document.getElementById('filtro_cep');
        if (filtroCepInput) {
            Inputmask('99999-999', { clearIncomplete: true }).mask(filtroCepInput);
        }
    }
});
</script>