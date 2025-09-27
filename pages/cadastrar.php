<?php
session_start();

// Protege a página: se o usuário não estiver logado, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro'] = "Você precisa fazer login para cadastrar um pedido.";
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Pedido - AjudaJá</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['sucesso'])) { echo "<div class='container mt-3'><div class='alert alert-success alert-dismissible fade show' role='alert'>" . $_SESSION['sucesso'] . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div></div>"; unset($_SESSION['sucesso']); } ?>
    <?php if (isset($_SESSION['erro'])) { echo "<div class='container mt-3'><div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $_SESSION['erro'] . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div></div>"; unset($_SESSION['erro']); } ?>

    <header class="header">
        <nav class="navbar navbar-dark bg-success">
            <div class="container">
                <a class="navbar-brand" href="index.php"><i class="fas fa-arrow-left"></i> Voltar à Home</a>
            </div>
        </nav>
    </header>

    <main class="container my-4">
        <h2 class="mb-4"><i class="fas fa-edit"></i> Cadastrar Pedido de Ajuda</h2>
        <form action="../includes/salvar_pedido.php" method="POST" id="form-cadastro" class="row g-3">
            <div class="col-md-12">
                <label class="form-label"><i class="fas fa-heading"></i> Título do Pedido</label>
                <input type="text" class="form-control" name="titulo" required maxlength="255" placeholder="Ex: Preciso de cesta básica urgente">
            </div>
            <div class="col-md-12">
                <label class="form-label"><i class="fas fa-align-left"></i> Descrição Detalhada</label>
                <textarea class="form-control" name="descricao" rows="5" required placeholder="Descreva o que precisa de ajuda, detalhes importantes..."></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="fas fa-clock"></i> Nível de Urgência</label>
                <select class="form-select" name="urgencia" required>
                    <option value="">Selecione uma opção</option>
                    <option value="Urgente">Urgente (Preciso agora ou em poucas horas)</option>
                    <option value="Pode Esperar">Pode Esperar (Em alguns dias)</option>
                    <option value="Daqui a uma Semana">Daqui a uma Semana (Não é imediato)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="fas fa-tags"></i> Categoria</label>
                <select class="form-select" name="categoria" required>
                    <option value="">Selecione uma categoria</option>
                    <option value="Cesta Básica">Cesta Básica</option>
                    <option value="Carona">Carona</option>
                    <option value="Apoio Emocional">Apoio Emocional</option>
                    <option value="Doação de Itens">Doação de Itens (Roupas, Móveis)</option>
                    <option value="Serviços Voluntários">Serviços Voluntários (Reparos, Aulas)</option>
                    <option value="Outros">Outros</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label"><i class="fab fa-whatsapp"></i> Seu Número de WhatsApp</label>
                <input type="tel" class="form-control" name="whatsapp" required placeholder="Ex: 11999999999 (DDD + Número)" pattern="\d{10,11}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success w-100"><i class="fas fa-paper-plane"></i> Postar Meu Pedido</button>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>