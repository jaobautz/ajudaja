<div align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"/>
  <img src="https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL"/>
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript"/>
  <img src="https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap"/>
</div>

<h1 align="center">
  AjudaJá
</h1>

<p align="center">
  <strong>Uma plataforma de ajuda comunitária robusta e segura, construída com PHP 8+ e PostgreSQL.</strong>
</p>

<p align="center">
  <a href="#-sobre-o-projeto">Sobre</a> •
  <a href="#-funcionalidades-em-destaque">Funcionalidades</a> •
  <a href="#-tecnologias-e-ferramentas">Tecnologias</a> •
  <a href="#-instalação-crítica">Instalação</a>
</p>

<div align="center">
  <img src="https://img.shields.io/badge/status-pronto_para_deploy-brightgreen?style=for-the-badge" alt="Status do Projeto"/>
</div>

---

### 💡 Sobre o Projeto
O **AjudaJá** é uma aplicação web completa e pronta para produção, projetada para conectar pessoas que precisam de ajuda com voluntários da comunidade. A plataforma é focada em segurança (CSRF, SQL Injection, Senhas Fortes), performance (Índices GIST/GIN no PostgreSQL) e uma experiência de usuário rica, incluindo chat interno, sistema de avaliação por estrelas e filtros de geolocalização por proximidade.

---

### ✨ Funcionalidades em Destaque

* **Autenticação Segura:** Cadastro com validação de senha forte, login seguro (previne Session Fixation) e recuperação de conta (a ser implementada).
* **CRUD de Pedidos:** Usuários criam e editam pedidos, com geocodificação automática de CEP para Cidade, Estado, Latitude e Longitude via APIs (ViaCEP, Nominatim).
* **Chat Interno Privado:** Sistema de mensagens diretas entre solicitante e voluntário, substituindo o WhatsApp para maior privacidade.
* **Sistema de Avaliação:** Solicitantes podem avaliar a ajuda recebida com 1-5 estrelas e um comentário, que fica visível no perfil público do voluntário.
* **Perfil Público:** Usuários podem ver a atividade de outros membros, incluindo suas avaliações recebidas (média de estrelas, total) e seus pedidos de ajuda criados.
* **Filtro por Proximidade:** A página inicial permite que voluntários busquem pedidos pelo *seu* CEP e um raio (ex: 10km), ordenando os resultados por distância.
* **Discussão Pública:** Sistema de comentários aninhados (threads) em cada pedido para dúvidas públicas.

---

### 🚀 Tecnologias e Ferramentas

* **Backend:** PHP 8+ (com extensões `pgsql`, `curl`, `mbstring`)
* **Frontend:** HTML5, CSS3 (Design System com Variáveis), JavaScript (ES6+), Bootstrap 5, Lucide Icons
* **Banco de Dados:** PostgreSQL 14+
* **Extensões PostgreSQL (Obrigatórias):** `pg_trgm`, `btree_gist`, `cube`, `earthdistance` (o script SQL tenta instalá-las).
* **APIs Externas:**
    * **ViaCEP:** Para validar CEPs e obter endereços.
    * **Nominatim (OpenStreetMap):** Para geocodificação (obter Lat/Lon).
* **Bibliotecas JS:** Inputmask.js (para máscaras de formulário)

---

### 🚨 Instalação (Crítica)

Siga estes passos **exatamente** para evitar erros.

**1. Clone o Repositório**
```bash
# Clone para uma pasta chamada 'ajudajaa' (com dois 'a's)
git clone [https://github.com/SEU_USUARIO/ajudajaa.git](https://github.com/SEU_USUARIO/ajudajaa.git)
cd ajudajaa
2. Configure o Banco de Dados

No PostgreSQL, crie um banco de dados vazio chamado ajudaja (com um 'a' só).

Abra a ferramenta de consulta e execute o script ajudaja.sql inteiro. Ele criará todas as tabelas, extensões e índices.

3. Configure o Projeto (Passo Mais Importante)

Você DEVE editar dois arquivos na pasta includes/:

includes/config.php:

Verifique se define('BASE_URL', ...) aponta para sua pasta (ex: http://localhost/ajudajaa).

Verifique se $dbname está como 'ajudaja'.

Altere a $password para a senha do seu usuário PostgreSQL local.

includes/geocoding.php:

A API Nominatim (OpenStreetMap) exige um User-Agent válido.

Encontre a linha: curl_setopt($ch, CURLOPT_USERAGENT, 'AjudaJaaApp/1.0 (seuemail@seudominio.com)');

MUDE (seuemail@seudominio.com) para seu e-mail real ou o site onde a aplicação será hospedada. Se não fizer isso, a geolocalização falhará.

4. Verifique as Dependências do Servidor

Certifique-se de que sua instalação do PHP tem as extensões php_pgsql (para conectar ao banco) e php_curl (para as APIs) habilitadas no seu php.ini.

5. Execute o Projeto

Acesse a URL definida na BASE_URL: http://localhost/ajudajaa/pages/