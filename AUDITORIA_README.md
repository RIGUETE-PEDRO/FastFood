# Sistema de Auditoria Global - FlashFood

## 📋 Visão Geral

Sistema de rastreamento completo de todas as ações dos usuários no FlashFood, incluindo:

- ✅ **Login/Logout** - Autenticação de usuários
- ✅ **CRUD Operations** - Criação, leitura, atualização, exclusão de recursos
- ✅ **Operações Sensíveis** - Pagamentos, mudanças de status, permissões
- ✅ **Acessos** - Visualizações de recursos
- ✅ **Alertas de Segurança** - Tentativas não autorizadas
- ✅ **IP Tracking** - Rastreia IP do cliente
- ✅ **User Agent** - Registra dispositivo/navegador

---

## 🏗️ Arquitetura

### Classes Principais

```
AuditoriaService
├── Métodos estáticos para registrar ações
├── Sanitização automática de dados sensíveis
└── Suporte para IP Cloudflare/Proxy

RegistrarAuditoria (Middleware)
├── Intercepta todas as requisições autenticadas
├── Filtra POST/PUT/PATCH/DELETE
├── Rotas importantes em GET
└── Registra IP, User Agent, parâmetros

RegistrarEventosAutenticacao (Listener)
├── Captura Login
├── Captura Logout
└── Captura tentativas falhadas
```

---

## 📝 Como Usar

### 1️⃣ Operações Básicas (Middleware automático)

Qualquer POST, PUT, PATCH ou DELETE é registrado automaticamente:

```php
// Será registrado automaticamente
$pedido = PedidoModel::create($dados);
$usuario->update($dados);
$produto->delete();
```

### 2️⃣ Registrar Ações Customizadas

```php
use App\Services\AuditoriaService;

// Criação
AuditoriaService::registrarCriacao('usuario', [
    'email' => 'novo@example.com',
    'nome' => 'João Silva',
]);

// Atualização
AuditoriaService::registrarAtualizacao('usuario', 
    ['status' => 'ativo'],
    ['status' => 'inativo']
);

// Exclusão
AuditoriaService::registrarExclusao('usuario', [
    'id' => 5,
    'email' => 'deletado@example.com',
]);

// Visualização
AuditoriaService::registrarVisualizacao('pedido', 123);
```

### 3️⃣ Ações Específicas

```php
// Login
AuditoriaService::registrarLogin($usuarioId, 'email');

// Logout
AuditoriaService::registrarLogout($usuarioId);

// Alteração de permissões
AuditoriaService::registrarAlteracaoPermissoes(
    usuarioIdAlvo: 10,
    rolesAntigos: ['user'],
    rolesNovos: ['user', 'admin'],
    usuarioIdAdmin: 1
);

// Pagamento
AuditoriaService::registrarPagamento(
    pedidoId: 100,
    status: 'aprovado',
    dadosPagamento: [
        'tipo' => 'cartao_credito',
        'valor' => 150.00,
        'referencia' => 'TXN123456',
    ]
);

// Mudança de status
AuditoriaService::registrarMudancaStatusPedido(
    pedidoId: 100,
    statusAntigo: 'pendente',
    statusNovo: 'confirmado',
    motivo: 'Pagamento aprovado'
);

// Carrinho
AuditoriaService::registrarAdicaoCarrinho(
    carrinhoId: 5,
    produtoId: 10,
    quantidade: 2,
    preco: 45.99
);

AuditoriaService::registrarRemocaoCarrinho(
    carrinhoId: 5,
    produtoId: 10,
    quantidade: 2
);

// Acesso negado
AuditoriaService::registrarAcessoNaoAutorizado(
    recurso: 'relatorios.vendas',
    motivo: 'Permissão insuficiente',
    usuarioId: 5
);
```

### 4️⃣ Em Serviços (Recomendado)

```php
use App\Services\AuditoriaService;
use App\Models\PedidoModel;

class PedidoService
{
    public function criarPedido(array $dados)
    {
        $pedido = PedidoModel::create($dados);
        
        AuditoriaService::registrarCriacao('pedido', [
            'id' => $pedido->id,
            'usuario_id' => $pedido->usuario_id,
            'valor_total' => $pedido->valor_total,
        ]);
        
        return $pedido;
    }
    
    public function atualizarStatusPedido($pedidoId, $statusNovo)
    {
        $pedido = PedidoModel::find($pedidoId);
        $statusAntigo = $pedido->status;
        
        $pedido->update(['status' => $statusNovo]);
        
        AuditoriaService::registrarMudancaStatusPedido(
            $pedidoId,
            $statusAntigo,
            $statusNovo
        );
    }
}
```

### 5️⃣ Em Controllers

```php
namespace App\Http\Controllers;

use App\Services\AuditoriaService;

class ProdutoController extends Controller
{
    public function excluir($id)
    {
        $produto = Produto::findOrFail($id);
        $dados = $produto->toArray();
        
        $produto->delete();
        
        AuditoriaService::registrarExclusao('produto', $dados);
        
        return redirect()->back()->with('success', 'Produto deletado');
    }
}
```

---

## 🔒 Segurança e Privacidade

### Dados Sensíveis Automaticamente Protegidos

O middleware sanitiza automaticamente:
- `senha`, `password`
- `token`, `secret`, `api_key`
- `numero_cartao`, `cvv`, `pin`
- E outras variações

**Exemplo:**
```php
// Entrada
['email' => 'user@example.com', 'password' => 'secret123']

// Registrado como
['email' => 'user@example.com', 'password' => '[REDACTED]']
```

### IP Detection

Suporta múltiplos cenários:
- ✅ Cloudflare (`HTTP_CF_CONNECTING_IP`)
- ✅ Proxies (`HTTP_X_FORWARDED_FOR`)
- ✅ IP Direto (`REMOTE_ADDR`)

---

## 📊 Visualizando Auditoria

### Na Interface Admin

Acesse: `/admin/keycloak/auditoria`

Filtros disponíveis:
- 📌 **Por Ação** - criar, atualizar, excluir, login, etc
- 👤 **Por Usuário** - Quem fez
- 📁 **Por Recurso** - pedido, usuario, produto, etc

### Via Query SQL

```sql
-- Últimas 50 ações
SELECT * FROM key_clock_auditoria 
ORDER BY created_at DESC 
LIMIT 50;

-- Ações de um usuário específico
SELECT * FROM key_clock_auditoria 
WHERE usuario_id = 1 
ORDER BY created_at DESC;

-- Ações de exclusão
SELECT * FROM key_clock_auditoria 
WHERE acao = 'excluir' 
ORDER BY created_at DESC;

-- Login/Logout
SELECT * FROM key_clock_auditoria 
WHERE acao IN ('login', 'logout') 
ORDER BY created_at DESC;

-- Pagamentos processados
SELECT * FROM key_clock_auditoria 
WHERE acao = 'processar_pagamento' 
ORDER BY created_at DESC;
```

---

## 🚀 Exemplo Completo

### Cenário: Criar e Processar Pedido

```php
<?php

use App\Services\AuditoriaService;
use App\Models\PedidoModel;

// 1. Usuário acessa pedidos (registrado por Middleware em GET importante)
AuditoriaService::registrarVisualizacao('pedidos');

// 2. Cria novo pedido (registrado automaticamente por Middleware)
$pedido = PedidoModel::create([
    'usuario_id' => auth()->id(),
    'valor_total' => 250.00,
    'status' => 'pendente',
]);
// ✅ Registrado: criar -> pedido

// 3. Adiciona itens ao carrinho
AuditoriaService::registrarAdicaoCarrinho(
    carrinhoId: auth()->user()->carrinho->id,
    produtoId: 5,
    quantidade: 2,
    preco: 125.00
);
// ✅ Registrado: adicionar_carrinho -> carrinho:123

// 4. Processa pagamento
AuditoriaService::registrarPagamento(
    pedidoId: $pedido->id,
    statusPagamento: 'aprovado',
    dadosPagamento: [
        'tipo' => 'cartao_credito',
        'valor' => 250.00,
        'referencia' => 'PAY123456',
    ]
);
// ✅ Registrado: processar_pagamento -> pedido:1

// 5. Muda status do pedido
AuditoriaService::registrarMudancaStatusPedido(
    pedidoId: $pedido->id,
    statusAntigo: 'pendente',
    statusNovo: 'confirmado',
    motivo: 'Pagamento aprovado'
);
// ✅ Registrado: mudanca_status_pedido -> pedido:1

// 6. Atualiza endereço (registrado automaticamente)
$endereco = auth()->user()->endereco->update(['cidade' => 'São Paulo']);
// ✅ Registrado: atualizar -> endereco
```

---

## ⚙️ Configuração

### Adicionar Novas Rotas Importantes

No middleware `RegistrarAuditoria.php`, edite o método `ehRotaImportante()`:

```php
private function ehRotaImportante(string $rota): bool
{
    $rotasImportantes = [
        'login',
        'logout',
        'password.update',
        'profile.update',
        'admin.',
        'keycloak.',
        'relatorios.',
        'minha_rota_nova', // ← Adicione aqui
    ];
    // ...
}
```

### Alterar Retenção de Dados

Adicione um job agendado para limpar registros antigos (optional):

```php
// app/Console/Commands/LimparAuditoriaAntiga.php
$dias = 90; // Manter 90 dias
KeyClockAuditoriaModel::where('created_at', '<', now()->subDays($dias))
    ->delete();
```

---

## 📌 Checklist de Implementação

- [x] `AuditoriaService` - Serviço central de registros
- [x] `RegistrarAuditoria` Middleware - Interceptação automática
- [x] `RegistrarEventosAutenticacao` - Login/Logout/Failed
- [x] `EventServiceProvider` - Registra listeners
- [x] `PedidoComAuditoriaService` - Exemplo de implementação
- [ ] `CarrinhoComAuditoriaService` - A ser criado
- [ ] `ProdutoComAuditoriaService` - A ser criado
- [ ] `UsuarioComAuditoriaService` - A ser criado
- [ ] Jobs agendados para limpeza de dados antigos
- [ ] Relatórios e dashboards de auditoria

---

## 🔍 Monitoramento e Alertas (Futuro)

```php
// Exemplo: Alertar sobre múltiplas tentativas falhadas
if (KeyClockAuditoriaModel::where('usuario_id', $userId)
    ->where('acao', 'login_falhou')
    ->where('created_at', '>', now()->subMinutes(10))
    ->count() >= 3) {
    
    // Bloquear usuário ou enviar alerta
    notificarAdministrador($userId);
}
```

---

## 📞 Suporte

Para questões ou sugestões sobre o sistema de auditoria, consulte a documentação do Laravel Events/Listeners ou entre em contato com o administrador do sistema.
