# AjudaJá

**Uma plataforma de ajuda comunitária construída com PHP e PostgreSQL.**

O **AjudaJá** é uma aplicação web completa e funcional, projetada para ser um ponto de encontro digital entre pessoas que precisam de ajuda e voluntários dispostos a oferecer apoio. O sistema permite que usuários criem, gerenciem e interajam com pedidos de ajuda de forma organizada e segura, fortalecendo os laços da comunidade.

-----

### Visão Geral do Projeto

*A imagem abaixo demonstra a interface principal do sistema, exibindo a listagem de pedidos na página inicial.*

-----

### ✨ Funcionalidades Essenciais

  - **Autenticação Segura:** Sistema completo de Cadastro e Login de usuários com senhas criptografadas, garantindo a segurança dos dados.
  - **Dashboard Pessoal:** Cada usuário possui um painel de controle exclusivo para gerenciar seus pedidos, acompanhar estatísticas e visualizar o engajamento através de gráficos dinâmicos.
  - **Gestão Completa de Pedidos (CRUD):** Usuários autenticados podem facilmente Criar, Editar e Excluir seus próprios pedidos de ajuda.
  - **Sistema de Comentários Interativo:** Uma seção de discussão em cada pedido permite que a comunidade interaja, tire dúvidas e ofereça apoio, com um contador de comentários visível na listagem principal.
  - **Busca e Filtragem Avançada:** Ferramentas intuitivas na página inicial para que voluntários encontrem facilmente os pedidos por palavra-chave, categoria ou nível de urgência.
  - **Contato Direto e Seguro:** Integração com o WhatsApp para facilitar a comunicação entre o voluntário e o solicitante sem expor informações desnecessárias na plataforma.
  - **Design Moderno e Responsivo:** Interface limpa e profissional, construída com Bootstrap 5, que se adapta perfeitamente a qualquer tamanho de tela, seja desktop, tablet ou celular.

-----

### 🛠️ Tecnologias Utilizadas

O projeto foi construído com uma pilha de tecnologias modernas e confiáveis:

  - **Backend:** PHP 8+
  - **Banco de Dados:** PostgreSQL 14+
  - **Frontend:**
      - HTML5 e CSS3 (com Design System via variáveis)
      - JavaScript (ES6+)
      - Bootstrap 5
  - **Ferramentas e APIs:**
      - **Chart.js:** Para a criação dos gráficos do dashboard.
      - **Lucide Icons:** Para a iconografia limpa e moderna.
      - **DiceBear:** Para a geração de avatares personalisados.

-----

### 🚀 Guia de Instalação

Siga os passos abaixo para configurar e executar o projeto em seu ambiente local.

**1. Pré-requisitos**

  - Ambiente de servidor local com PHP e PostgreSQL.
  - Ferramenta de gerenciamento de banco de dados (ex: pgAdmin).
  - Git instalado.

**2. Clone o Repositório**

```bash
git clone https://github.com/SEU_USUARIO/ajudaja.git
cd ajudaja
```

**3. Configure o Banco de Dados**

  - No pgAdmin, crie um novo banco de dados vazio chamado `ajudaja`.
  - Abra a ferramenta de consulta para este banco, copie todo o conteúdo do arquivo `ajudaja.sql` e execute o script.

**4. Configure a Conexão**

  - Abra o arquivo `includes/config.php`.
  - Altere a variável `$password` para a senha do seu usuário do PostgreSQL.

**5. Execute o Projeto**

  - Mova a pasta do projeto para o diretório raiz do seu servidor web (ex: `htdocs`).
  - Inicie os serviços Apache e PostgreSQL.
  - Acesse no seu navegador: `http://localhost/ajudaja/pages/`