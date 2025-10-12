<?php
$page_title = 'Cadastrar Pedido - AjudaJá';
include '../includes/autenticacao.php'; 
include '../includes/header.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="display-6 fw-bold">Novo Pedido de Ajuda</h1>
                <p class="lead text-secondary">Descreva sua necessidade para que a comunidade possa ajudar.</p>
            </div>
            <div class="card p-4 p-md-5" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                <form action="../includes/salvar_pedido.php" method="POST" id="form-cadastro" class="row g-4">
                    <div class="col-md-12 form-floating">
                        <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="255" placeholder="Título do Pedido">
                        <label for="titulo">Título do Pedido</label>
                    </div>
                    <div class="col-md-12 form-floating">
                        <textarea class="form-control" id="descricao" name="descricao" required placeholder="Descrição Detalhada" style="height: 150px;"></textarea>
                        <label for="descricao">Descrição Detalhada</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <select class="form-select" id="urgencia" name="urgencia" required>
                            <option value="" disabled selected>Selecione uma opção</option>
                            <option value="Urgente">Urgente</option>
                            <option value="Pode Esperar">Pode Esperar</option>
                            <option value="Daqui a uma Semana">Daqui a uma Semana</option>
                        </select>
                        <label for="urgencia">Nível de Urgência</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <select class="form-select" id="categoria" name="categoria" required>
                             <option value="" disabled selected>Selecione uma categoria</option>
                            <option value="Cesta Básica">Cesta Básica</option>
                            <option value="Carona">Carona</option>
                            <option value="Apoio Emocional">Apoio Emocional</option>
                            <option value="Doação de Itens">Doação de Itens</option>
                            <option value="Serviços Voluntários">Serviços Voluntários</option>
                            <option value="Outros">Outros</option>
                        </select>
                        <label for="categoria">Categoria</label>
                    </div>
                    <div class="col-md-12 form-floating">
                        <input type="tel" class="form-control" id="whatsapp" name="whatsapp" required placeholder="Seu Número de WhatsApp" pattern="\d{10,15}">
                        <label for="whatsapp">Seu Número de WhatsApp</label>
                    </div>
                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-success w-100 py-3"><i data-lucide="send"></i> Postar Meu Pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>