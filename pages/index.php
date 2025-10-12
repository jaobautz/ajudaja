<?php 
$page_title = 'AjudaJá - Home';
include '../includes/config.php'; 
include '../includes/header.php';

$categorias = [];
$result_cat = pg_query($conn, "SELECT DISTINCT categoria FROM pedidos ORDER BY categoria");
if ($result_cat) {
    while ($row = pg_fetch_assoc($result_cat)) {
        $categorias[] = $row['categoria'];
    }
}
?>

<main class="container my-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Ajuda Comunitária ao seu Alcance</h1>
        <p class="lead text-secondary">Encontre ou ofereça ajuda de forma rápida e segura na sua comunidade.</p>
    </div>
    
    <form action="index.php" method="GET" class="row mb-5 g-3 align-items-end filter-form">
        <div class="col-md-5">
            <label for="busca" class="form-label">Buscar por termo</label>
            <input type="text" class="form-control" id="busca" name="busca" placeholder="Ex: cesta básica, reparos..." value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
        </div>
        <div class="col-md-3">
            <label for="filtro-categoria" class="form-label">Categoria</label>
            <select class="form-select" id="filtro-categoria" name="categoria">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="urgencia" class="form-label">Urgência</label>
            <select class="form-select" id="urgencia" name="urgencia">
                <option value="">Todas</option>
                <option value="Urgente" <?php echo (isset($_GET['urgencia']) && $_GET['urgencia'] == 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                <option value="Pode Esperar" <?php echo (isset($_GET['urgencia']) && $_GET['urgencia'] == 'Pode Esperar') ? 'selected' : ''; ?>>Pode Esperar</option>
                <option value="Daqui a uma Semana" <?php echo (isset($_GET['urgencia']) && $_GET['urgencia'] == 'Daqui a uma Semana') ? 'selected' : ''; ?>>Daqui a uma Semana</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">
                <i data-lucide="search"></i> Filtrar
            </button>
        </div>
    </form>
    
    <div id="lista-pedidos" class="row">
        <?php include '../includes/lista_pedidos.php'; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>