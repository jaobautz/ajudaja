AjudaJá - Plataforma de Ajuda Comunitária
Uma aplicação web moderna desenvolvida em PHP e PostgreSQL, projetada para conectar pessoas que precisam de ajuda com voluntários da comunidade. A plataforma tem como objetivo fomentar o apoio mútuo, permitindo que usuários cadastrados criem, gerenciem e interajam com pedidos de ajuda de forma organizada, segura e acessível.

✨ Funcionalidades Principais
👤 Autenticação Completa: Sistema seguro de Login e Cadastro de usuários, com senhas criptografadas (password_hash) e gerenciamento de sessão.

📝 CRUD Completo de Pedidos: Usuários logados podem Criar, Ler, Atualizar (Editar) e Excluir seus próprios pedidos de ajuda.

📊 Dashboard Interativo: Painel de controle privado para cada usuário, com:

Estatísticas visuais sobre seus pedidos (Total, Abertos, Urgentes, Concluídos).

Gráficos dinâmicos de distribuição por Categoria e Urgência.

Gerenciamento completo de todos os seus pedidos.

💬 Sistema de Comentários: Seção de discussão em cada pedido para que a comunidade possa fazer perguntas, tirar dúvidas e oferecer apoio.

🔍 Filtros e Busca Avançada: Ferramentas na página inicial para filtrar pedidos por termo de busca, categoria e nível de urgência.

📱 Contato via WhatsApp: Botão de contato direto em cada pedido para facilitar a comunicação segura e imediata entre o voluntário e o solicitante.

🖼️ Avatares Dinâmicos: Geração automática de avatares com as iniciais dos usuários (via DiceBear API) para personalizar a identificação visual.

🎨 Design Moderno e Responsivo: Interface limpa e profissional, construída com Bootstrap 5 e um design system próprio, garantindo uma ótima experiência em desktops e dispositivos móveis.

🚀 Tecnologias Utilizadas
Backend: PHP 8+

Frontend: HTML5, CSS3, JavaScript (ES6+)

Banco de Dados: PostgreSQL 14+

Frameworks / Bibliotecas:

Bootstrap 5

Chart.js (para gráficos)

Lucide Icons (para iconografia)

Serviços/APIs:

DiceBear (para geração de avatares)

Google Fonts (fonte 'Inter')

⚙️ Como Instalar e Executar o Projeto
Pré-requisitos:

Um ambiente de servidor local com PHP e PostgreSQL (ex: XAMPP com PostgreSQL, Laragon, Docker, etc.).

Uma ferramenta de gerenciamento de banco de dados para PostgreSQL (ex: pgAdmin).

Git instalado (opcional, para clonar o repositório).

Passos:

Obtenha os Arquivos do Projeto:

Abra seu terminal, navegue até o diretório onde deseja salvar o projeto e clone o repositório:

Bash

git clone https://github.com/SEU_USUARIO/ajudaja.git
Alternativa: Baixe o projeto como um arquivo .zip e extraia-o.

Configure o Banco de Dados:

Abra o pgAdmin (ou sua ferramenta de preferência).

Crie um novo banco de dados vazio e nomeie-o como ajudaja.

Abra a "Query Tool" (Ferramenta de Consulta) para o banco ajudaja.

Copie todo o conteúdo do arquivo ajudaja.sql do projeto.

Cole o conteúdo na Query Tool e execute o script. Isso criará todas as tabelas e inserirá os dados de exemplo.

Configure a Conexão PHP:

Navegue até a pasta includes/ e abra o arquivo config.php.

Localize as variáveis de conexão e preencha com suas credenciais do PostgreSQL (principalmente a $password).

PHP

$host = "localhost";
$port = "5432";
$dbname = "ajudaja";
$user = "postgres";
$password = "sua_senha_aqui"; // <-- ALTERE AQUI
Execute o Projeto:

Mova a pasta inteira do projeto (ajudaja) para o diretório raiz do seu servidor web (geralmente htdocs no XAMPP ou www no Laragon).

Certifique-se de que os serviços Apache e PostgreSQL estejam em execução.

Abra seu navegador e acesse o endereço:

http://localhost/ajudaja/pages/index.php
🗂️ Estrutura de Arquivos Completa
/ajudaja/
│
├── css/
│   └── style.css               # Folha de estilo principal (Design System)
│
├── includes/
│   ├── autenticacao.php        # Verifica se o usuário está logado
│   ├── atualizar_status.php    # Atualiza o status de um pedido (AJAX)
│   ├── config.php              # Configuração da conexão com o banco (PostgreSQL)
│   ├── excluir_pedido.php      # Processa a exclusão de um pedido
│   ├── footer.php              # Rodapé padrão das páginas
│   ├── header.php              # Cabeçalho padrão e menu de navegação
│   ├── lista_pedidos.php       # Lógica para buscar e exibir a lista de pedidos na home
│   ├── logout.php              # Finaliza a sessão do usuário
│   ├── processa_comentario.php # Salva um novo comentário no banco
│   ├── processa_edicao.php     # Salva as alterações de um pedido editado
│   ├── processa_login.php      # Valida os dados de login do usuário
│   ├── processa_registro.php   # Cadastra um novo usuário
│   └── salvar_pedido.php       # Salva um novo pedido no banco
│
├── js/
│   └── script.js               # Scripts do lado do cliente (AJAX, Gráficos, etc.)
│
├── pages/
│   ├── cadastrar.php           # Formulário para criar um novo pedido
│   ├── dashboard.php           # Painel de controle do usuário com estatísticas e gráficos
│   ├── editar_pedido.php       # Formulário para editar um pedido existente
│   ├── index.php               # Página inicial e listagem de todos os pedidos
│   ├── login.php               # Formulário de login
│   ├── pedido_detalhe.php      # Exibe os detalhes de um pedido e os comentários
│   └── registrar.php           # Formulário de cadastro de novos usuários
│
├── ajudaja.sql                 # Script do banco de dados (PostgreSQL)
└── README.md                   # Manual de instruções e documentação do projeto