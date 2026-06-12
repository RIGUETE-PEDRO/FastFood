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
```

### Executando o Cypress

Para abrir os testes end-to-end:

```bash
npx cypress open
```
