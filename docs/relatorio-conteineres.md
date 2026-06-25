fazer piscar o pedido novo ajuda 


# Relatório dos contêineres do FlashFood

Data da verificação: 18 de junho de 2026.

O projeto utiliza Docker Compose e possui três contêineres ativos, conectados pela rede `flashfood_default`.

## Contêiner 1 — Node.js e Vite

- Nome: `node_FlashFood`
- Serviço no Compose: `node`
- Imagem: `node:22`
- Estado verificado: ativo
- Diretório de trabalho: `/app`
- Porta: `5173` no computador para `5173` no contêiner
- Endereço: `http://localhost:5173`
- Comando executado:

```sh
npm install && npm run dev -- --host 0.0.0.0
```

### Função

Este contêiner executa o frontend em modo de desenvolvimento usando Vite. Ele processa os arquivos:

- `resources/css/app.css`
- `resources/js/app.js`

Também disponibiliza atualização automática da página durante o desenvolvimento.

### Principais tecnologias

- Node.js 22
- Vite 7
- Tailwind CSS 4
- Bootstrap 5
- Chart.js
- Axios
- Cypress

### Armazenamento

- A pasta do projeto no Windows é montada em `/app`.
- A pasta `/app/node_modules` fica em um volume Docker separado.

Assim, o contêiner acessa o mesmo código-fonte do computador, mas mantém suas próprias dependências Node.

## Contêiner 2 — Laravel e PHP

- Nome: `laravel_flashfood`
- Serviço no Compose: `app`
- Imagem: `flashfood-app`, construída pelo `Dockerfile`
- Estado verificado: ativo
- Diretório de trabalho: `/app`
- Porta: `8000` no computador para `8000` no contêiner
- Endereço: `http://localhost:8000`
- Comando executado:

```sh
test -f vendor/autoload.php || composer install &&
php artisan serve --host=0.0.0.0 --port=8000
```

### Função

Este é o contêiner principal da aplicação. Ele executa o backend Laravel e atende as requisições HTTP do sistema FlashFood.

### Conteúdo da imagem

- PHP 8.3 CLI
- Composer
- Extensões PHP `pdo`, `pdo_mysql` e `zip`
- Git, cURL e Unzip
- Laravel 12
- Código-fonte completo do projeto

### Armazenamento

- A pasta do projeto no Windows é montada em `/app`.
- As dependências PHP ficam no volume `flashfood_vendor_data`, montado em `/app/vendor`.

### Dependência

O serviço declara dependência do contêiner de banco de dados. Isso faz o Docker iniciar o MySQL antes do contêiner Laravel, embora não garanta sozinho que o banco já esteja pronto para aceitar conexões.

## Contêiner 3 — Banco de dados MySQL

- Nome: `mysql_container`
- Serviço no Compose: `db`
- Imagem: `mysql:8.0`
- Estado verificado: ativo
- Porta interna: `3306`
- Porta de acesso pelo computador: `3307`
- Banco criado: `FlashFood`
- Usuário utilizado pela aplicação: `root`
- Política de reinicialização: `always`

### Função

Este contêiner armazena os dados persistentes da aplicação Laravel.

Dentro da rede Docker, o Laravel utiliza:

```env
DB_HOST=db
DB_PORT=3306
DB_DATABASE=FlashFood
DB_USERNAME=root
```

Programas executados diretamente no computador, como MySQL Workbench ou DBeaver, devem acessar:

```text
Host: localhost
Porta: 3307
Banco: FlashFood
```

### Armazenamento

Os arquivos do MySQL ficam no volume `flashfood_db_data`, montado em `/var/lib/mysql`. Portanto, os dados não são apagados quando o contêiner é apenas parado ou recriado.

## Comunicação entre os contêineres

```text
Navegador
   ├── localhost:8000 → Laravel/PHP
   └── localhost:5173 → Node/Vite

Laravel/PHP
   └── db:3306 → MySQL

Todos os serviços
   └── rede flashfood_default
```

O nome `db` funciona como endereço do MySQL somente entre os contêineres da rede Docker. No Windows, o endereço correspondente é `localhost:3307`.

## Resumo dos volumes

| Volume | Usado por | Conteúdo |
|---|---|---|
| Pasta local montada em `/app` | Laravel e Node | Código-fonte do projeto |
| `flashfood_vendor_data` | Laravel | Pacotes instalados pelo Composer |
| Volume de `/app/node_modules` | Node | Pacotes instalados pelo NPM |
| `flashfood_db_data` | MySQL | Bancos, tabelas e registros |

## Resumo geral

| Nº | Contêiner | Tecnologia | Responsabilidade | Porta no computador |
|---:|---|---|---|---:|
| 1 | `node_FlashFood` | Node.js 22 e Vite | Compilar e servir o frontend | 5173 |
| 2 | `laravel_flashfood` | PHP 8.3 e Laravel 12 | Executar o backend e as regras do sistema | 8000 |
| 3 | `mysql_container` | MySQL 8.0 | Armazenar os dados da aplicação | 3307 |

