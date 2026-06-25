# FlashFood
O sistema foi desenvolvido com foco nas necessidades diárias de uma lanchonete, oferecendo uma solução de gerenciamento prática, moderna e eficiente. Sua proposta é facilitar a administração das operações do estabelecimento, proporcionando maior agilidade nos processos e reduzindo as dificuldades frequentemente encontradas na adoção de sistemas informatizados, especialmente aquelas relacionadas à resistência ao uso de novas tecnologias.

Além de simplificar a rotina operacional, o sistema tem como objetivo contribuir para uma gestão mais organizada, permitindo um melhor controle de vendas, produtos e processos internos. Dessa forma, busca-se auxiliar o estabelecimento a aumentar sua eficiência, melhorar a tomada de decisões e potencializar seus resultados financeiros por meio do uso dessa ferramenta tecnológica.
## Requisitos do Sistema
Para executar o projeto FlashFood, recomenda-se utilizar Docker, pois todas as principais dependências do sistema já estão configuradas nos contêineres.

### Requisitos com Docker

- Docker
- Docker Compose
- Portas disponíveis:
  - `8000` para a aplicação Laravel
  - `8080` para o WebSocket/Reverb
  - `5173` para o Vite
  - `3307` para o MySQL no computador host

Com Docker, o projeto utiliza:

- PHP 8.3
- Composer
- MySQL 8.0
- Node.js 22
- NPM
- Laravel 12
- Vite 7

### Requisitos sem Docker

Caso o projeto seja executado diretamente na máquina, sem contêineres, será necessário instalar:

- PHP 8.2 ou superior
- Composer 2.x
- MySQL 8.0
- Node.js 22 ou superior
- NPM
- Git

Extensões PHP necessárias:

- `pdo`
- `pdo_mysql`
- `zip`
- `openssl`
- `fileinfo`
- `xml`
- `dom`
- `tokenizer`
- `session`

Dependências principais do backend:

- Laravel 12
- Laravel Tinker
- PHPUnit 11

Dependências principais do frontend:

- Vite 7
- Tailwind CSS 4
- Bootstrap 5.3
- Chart.js
- Axios
- Cypress 15
- MySQL2
- Concurrently

## Ambientação do Sistema

### Executando com Docker

Na raiz principal do projeto, execute:

```bash
docker compose up
```

A aplicação ficará disponível em:

```bash
http://localhost:8000
```

O Vite ficará disponível na porta:

```bash
http://localhost:5173
```

O WebSocket/Reverb ficara disponivel na porta:

```bash
ws://localhost:8080
```

### Executando sem Docker

Na raiz do projeto, execute:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
php artisan reverb:start --host=0.0.0.0 --port=8080
```

Tambem e possivel subir Laravel, Reverb, fila, logs e Vite juntos com:

```bash
composer dev
```

### Debugando no VS Code

O projeto ja possui configuracoes em `.vscode/` para Xdebug. Instale a extensao recomendada `PHP Debug` quando o VS Code sugerir.

Para debug com Docker:

1. No painel Run and Debug, selecione `Listen for Xdebug` e clique em iniciar.
2. No VS Code, execute a task `Docker: compose up debug`.

Tambem e possivel iniciar pelo terminal:

```bash
docker compose up --build
```

3. Acesse `http://localhost:8000`.

O Node usado pelo Vite fica no container `node_FlashFood`. Para subir somente o Vite via Docker, execute a task `Docker: Vite dev`.

Se alterar `.vscode/launch.json`, pare o listener de debug no botao vermelho do VS Code e inicie `Listen for Xdebug` novamente. O VS Code so recarrega o mapeamento `/app -> workspace` quando a sessao de debug reinicia.

Para debug local no Windows, sem Docker:

1. Deixe MySQL disponivel em `127.0.0.1:3307`.
2. Instale Node.js 22 ou superior no Windows.
3. No VS Code, execute a task `Laravel: full local debug stack`.
4. No painel Run and Debug, selecione `Listen for Xdebug` e clique em iniciar.
5. Acesse `http://127.0.0.1:8000` e use breakpoints normalmente.

Se o breakpoint nao parar no Docker, confira se o listener `Listen for Xdebug` foi iniciado antes de abrir a pagina. O perfil `Debug Xdebug smoke test` testa apenas o Xdebug local do Windows; ele nao testa o Xdebug dentro do container.

O script `composer dev` continua otimizado e desliga o Xdebug. Para iniciar o ambiente local com Xdebug pelo terminal, use:

```bash
composer debug
```

### Executando o Cypress

Para abrir os testes end-to-end:

```bash
npx cypress open
```
