# AjudaJá - Plataforma de Ajuda Comunitária

[![Status do Projeto](https://img.shields.io/badge/status-em%20desenvolvimento-brightgreen)](https://github.com/SEU_USUARIO/ajudaja)

Uma aplicação web desenvolvida em PHP para conectar pessoas que precisam de ajuda com voluntários da comunidade. A plataforma permite que usuários cadastrados criem, gerenciem e visualizem pedidos de ajuda de forma organizada e acessível.

---

### ✨ Funcionalidades Principais

* **👤 Autenticação de Usuários:** Sistema completo de Login e Cadastro de usuários, com senhas criptografadas para segurança.
* **🖼️ Avatares Dinâmicos:** Geração automática de avatares com as iniciais dos usuários, adicionando um toque pessoal a cada pedido.
* **📝 Criação e Gestão de Pedidos:** Usuários logados podem criar pedidos de ajuda detalhados, classificando-os por categoria e nível de urgência.
* **📊 Dashboard Pessoal:** Cada usuário tem um painel de controle privado para visualizar estatísticas e gerenciar *apenas os seus próprios* pedidos.
* **📄 Arquitetura Lista/Detalhes:** A página inicial exibe um resumo dos pedidos para uma navegação rápida. Ao clicar, o usuário é levado a uma página com todos os detalhes daquela solicitação.
* **🔍 Filtros e Busca:** Ferramentas na página inicial para filtrar os pedidos por termo de busca ou nível de urgência.
* **📱 Contato via WhatsApp:** Integração direta com o WhatsApp para facilitar o contato entre quem ajuda e quem precisa de ajuda.

---

### 🚀 Tecnologias Utilizadas

* **Backend:** PHP
* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
* **Banco de Dados:** MySQL
* **Serviços/APIs:**
    * **DiceBear:** Para a geração automática de avatares.
    * **Chart.js:** Para a criação dos gráficos no dashboard.

---

### ⚙️ Como Instalar e Executar o Projeto

**Pré-requisitos:**
* Um ambiente de servidor local (XAMPP, WAMP, etc.)
* Git instalado na sua máquina.

**Passos:**

1.  **Clone o repositório:**
    ```bash
    git clone [https://github.com/SEU_USUARIO/ajudaja.git](https://github.com/SEU_USUARIO/ajudaja.git)
    ```

2.  **Configure o Banco de Dados:**
    * Crie um banco de dados no seu MySQL chamado `ajudaja`.
    * Importe o arquivo `ajudaja.sql` para dentro deste banco de dados. Ele criará todas as tabelas e adicionará dados de exemplo.

3.  **Inicie o Servidor:**
    * Coloque a pasta do projeto (`ajudaja`) dentro do diretório do seu servidor web (ex: `htdocs` no XAMPP).
    * Inicie os módulos Apache e MySQL do seu servidor.

4.  **Acesse a Aplicação:**
    * Abra seu navegador e acesse: `http://localhost/ajudajaa/pages/index.php`

---

### 🗂️ Estrutura de Arquivos Principal

/ajudajaa
├── css/
│   └── style.css
├── includes/
│   ├── config.php
│   ├── lista_pedidos.php
│   ├── processa_login.php
│   ├── processa_registro.php
│   └── ...
├── js/
│   └── script.js
├── pages/
│   ├── index.php           # Página inicial (lista)
│   ├── cadastrar.php
│   ├── dashboard.php
│   ├── login.php
│   ├── registrar.php
│   └── pedido_detalhe.php  # Página de detalhes
├── ajudaja.sql
└── README.md