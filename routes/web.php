<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GerenciamentoUsuarioController;
use App\Http\Controllers\GerenciamentoProdutoController;
use App\Http\Controllers\LanchesController;
use App\Http\Controllers\PizzaController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\BebidasController;
use App\Http\Controllers\PorcaoController;
use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidosFeitosController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\SecureKeyController;
use App\Http\Controllers\GarcomController;
use App\Http\Controllers\EntregasController;
use App\Roles\Roles;


///////////////////////////////////////////////////////
/* SecureKey */
///////////////////////////////////////////////////////
Route::middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::SecureKey])->group(function () {
    Route::prefix('SecureKey')->group(function () {
        Route::get('/', [SecureKeyController::class, 'index'])->name('SecureKey.index');
        Route::get('/grupo', [SecureKeyController::class, 'grupo'])->name('SecureKey.grupo');
        Route::get('/permissoes', [SecureKeyController::class, 'permissoes'])->name('SecureKey.permissoes');
        Route::get('/auditoria', [SecureKeyController::class, 'auditoria'])->name('SecureKey.auditoria');
    });
});

Route::middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::SecureKey])->group(function () {
    Route::post('/permissoes', [SecureKeyController::class, 'permissoes'])->name('SecureKey.permissoes.store');
    Route::post('/SecureKey/grupo/{grupo}/roles', [SecureKeyController::class, 'adicionarRoleGrupo'])
        ->name('SecureKey.grupo.roles.store');
    Route::delete('/SecureKey/grupo/{grupo}/roles/{role}', [SecureKeyController::class, 'removerRoleGrupo'])
        ->name('SecureKey.grupo.roles.destroy');
});



///////////////////////////////////////////////////////
/* GERENCIAMENTO DE PERFIL */
///////////////////////////////////////////////////////

Route::get('/', [IndexController::class, 'index'])->name('home');

// Teste Carrossel Simplificado
Route::get('/test-carousel-simple', function () {
    $produtos = \App\Models\ProdutoModel::where('disponivel', true)->get();
    return view('IndexSimplificado', ['produtos' => $produtos]);
})->name('test.carousel.simple');

// Teste Carrossel
Route::get('/test-carousel', function () {
    $produtos = \App\Models\ProdutoModel::where('disponivel', true)->get();
    return view('TestCarousel', ['produtos' => $produtos]);
})->name('test.carousel');

//perfil
Route::post('/perfil', [AdminController::class, 'InfoPerfil'])->name('usuario')->middleware('auth');

//login
Route::get('/login', function () {
    return view('Login');
})->name('login.form');

//cadastro
Route::get('/registro', function () {
    return view('Cadastrar');
})->name('registro.form');

//lista de produtos
Route::get('/Lista_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])->name('ListaProdutos');

//cadastro de funcionário
Route::post('/CadastrarFuncionario', [RegisterController::class, 'registerFuncionario'])
    ->name('CadastrarFuncionario')
    ->middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GERENCIAMENTO_FUNCIONARIOS]);

//esqueci minha senha
Route::get('/esqueci-senha', function () {
    return view('Esqueci-senha');
})->name('senha.form');

//redifinir senha
Route::get('/redefinir-senha', function () {
    return view('Redefinir-senha');
})->name('senha.redefinir.form');


//logout
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

//perfil
Route::get('/perfil', [AdminController::class, 'InfoPerfil'])->name('perfil')->middleware('auth');

//buscar funcionários
Route::get('/funcionarios/buscar', [GerenciamentoUsuarioController::class, 'buscarFuncionarios'])
    ->name('funcionarios.buscar')
    ->middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GERENCIAMENTO_FUNCIONARIOS]);
/////////////////////////////////////////////////////////


///////////////////////////////////////////////////////
/* PROCESSO DOS PEDIDOS */
///////////////////////////////////////////////////////
//administrativo
Route::middleware(['auth', 'admin.access'])->group(function () {
    Route::get('/admin/bem-vindo', [AdminController::class, 'bemVindo'])
        ->name('admin.bemvindo');

    Route::get('/admin/configuracoes', [AdminController::class, 'configuracoes'])
        ->middleware('admin.access:3')
        ->name('admin.configuracoes');

    Route::get('/Administrativo', [AdminController::class, 'nomeUsuario'])
        ->middleware('SecureKey.role:' . Roles::DASHBORD)
        ->name('Administrativo');

    Route::get('/Pedidos_Administrativo', [PedidosFeitosController::class, 'verPedidosAdmin'])
        ->middleware('SecureKey.role:' . Roles::PEDIDOS)
        ->name('Pedidos_Administrativo');
    Route::get('/admin/pedidos', [PedidoController::class, 'pedidosFiltro'])
    ->middleware('SecureKey.role:' . Roles::PEDIDOS)
    ->name('admin.pedidos');

    Route::get('/entregas', [EntregasController::class, 'index'])
        ->middleware('SecureKey.role:' . Roles::ENTREGAS)
        ->name('entregas');

    Route::post('/entregas/{pedido}/aceitar', [EntregasController::class, 'aceitar'])
        ->middleware('SecureKey.role:' . Roles::ENTREGAS)
        ->name('entregas.aceitar');

    Route::post('/entregas/{pedido}/finalizar', [EntregasController::class, 'finalizar'])
        ->middleware('SecureKey.role:' . Roles::ENTREGAS)
        ->name('entregas.finalizar');

    Route::patch('/Pedidos/Administrativo/{pedido}/status', [PedidosFeitosController::class, 'atualizarStatus'])
        ->middleware('SecureKey.role:' . Roles::PEDIDOS)
        ->name('Pedidos.StatusAtualizar');

    Route::post('/Pedidos/Administrativo/{pedido}/avancar', [PedidosFeitosController::class, 'avancarStatus'])
        ->middleware('SecureKey.role:' . Roles::PEDIDOS)
        ->name('Pedidos.StatusAvancar');

    Route::get('/Pedidos.Administrativo/poll', [PedidosFeitosController::class, 'pollResumo'])
        ->middleware('SecureKey.role:' . Roles::PEDIDOS)
        ->name('Pedidos.Poll');

    Route::get('GerarCupom/{pedido}', [PedidosFeitosController::class,'gerarCumpom' ])
        ->middleware('SecureKey.role:' . Roles::PEDIDOS)
        ->name('Pedidos.GerarCupom');


});

Route::middleware(['auth'])->group(function () {
    Route::get('/pedidos', [PedidoController::class, 'verPedido'])->name('pedidos');
});

///////////////////////////////////////////////////////
/* GERENCIAMENTO DE PRODUTOS */
///////////////////////////////////////////////////////
Route::post('/gerenciamento_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])
    ->name('gerenciamento_produtos')
    ->middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GERENCIAMENTO_PRODUTOS]);

Route::post('/Cadastrar_Produto', [GerenciamentoProdutoController::class, 'cadastrarProduto'])
    ->name('Cadastrar_Produto')
    ->middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GERENCIAMENTO_PRODUTOS]);

Route::get('/gerenciamento_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])
    ->name('gerenciamento_Produtos')
    ->middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GERENCIAMENTO_PRODUTOS]);

Route::post('/gerenciamento_Produtos/{id}/deletar', [GerenciamentoProdutoController::class, 'deletarProduto'])
    ->name('deletar_produto')
    ->middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GERENCIAMENTO_PRODUTOS]);

/* API - Carrousel Toggle */
Route::post('/api/produtos/{id}/carrousel', [GerenciamentoProdutoController::class, 'toggleCarrousel'])
    ->middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GERENCIAMENTO_PRODUTOS])
    ->name('produtos.carrousel.toggle');

///////////////////////////////////////////////////////
/* GERENCIAMENTO DO USUARIO */
///////////////////////////////////////////////////////
Route::get('/Lanches', [LanchesController::class, 'Lanches'])->name('Lanches');

Route::get('/Pizza', [PizzaController::class, 'Pizza'])->name('Pizza');

Route::get('/Bebidas', [BebidasController::class, 'Bebidas'])->name('Bebidas');

Route::get('/porcao', [PorcaoController::class, 'porcao'])->name('Porcao');


///////////////////////////////////////////////////////
/* GERENCIAMENTO DE GARCOM */
///////////////////////////////////////////////////////

Route::middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GARCOM])->group(function () {
    Route::get('/garcom', [GarcomController::class, 'index'])->name('garcom');

});
///////////////////////////////////////////////////////
/* GERENCIAMENTO DE CARRINHO */
///////////////////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('/carrinho', [CarrinhoController::class, 'verCarrinho'])->name('carrinho');

    Route::post('/carrinho/adicionar', [CarrinhoController::class, 'adicionarAoCarrinho'])->name('carrinho.adicionar');

    Route::post('/carrinho/endereco', [CarrinhoController::class, 'pegarEndereco'])->name('carrinho.endereco');
    Route::post('/carrinho/pagamento', [CarrinhoController::class, 'registrarPedido'])->name('carrinho.Pedido');

    Route::post('/carrinho/selecionarCidade', [CarrinhoController::class, 'selecionarCidade'])->name('cidade.buscar');


    Route::post('/carrinho/{id}/remover', [CarrinhoController::class, 'removerDoCarrinho'])->name('carrinho.remover');

    Route::put('/carrinho/{id}/atualizarQuantidade', [CarrinhoController::class, 'atualizarQuantidade'])->name('carrinho.atualizarQuantidade');

    Route::put('/carrinho/{id}/selecionar', [CarrinhoController::class, 'toggleSelecionar'])->name('carrinho.toggle');

    Route::get('/carrinho/{id}/deletar', function ($id) {
        return redirect()->route('carrinho');
    });

    Route::match(['post', 'delete'], '/carrinho/{id}/deletar', [CarrinhoController::class, 'deletarEndereco'])->name('endereco.excluir');

    Route::post('/carrinho/mesa', [CarrinhoController::class, 'selecionarMesa'])->name('carrinho.mesa');
});
///////////////////////////////////////////////////////
/* GERENCIAMENTO DE MESAS */
///////////////////////////////////////////////////////
Route::middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::MESAS])->group(function () {
    // Listagem das mesas (Página Principal)
    Route::get('/mesas', [MesaController::class, 'Mesa'])->name('mesas.index');

    // Cadastro de nova mesa
    Route::post('/mesas/cadastrar', [MesaController::class, 'cadastrarMesa'])->name('mesas.store');

    // Remoção de mesa (Usando POST para facilitar o uso com o <select>)
    // Alteramos para POST para que o ID venha de dentro do formulário, não da URL
    Route::post('/mesas/remover', [MesaController::class, 'removerMesa'])->name('mesas.destroy');

    // Rota para atualizar mesa (se necessário, pode ser implementada depois)
    Route::post('/mesas/atualizar', [MesaController::class, 'atualizarMesa'])->name('mesas.update');

    Route::get('/mesas/{id}', [MesaController::class, 'detalhesMesa'])->name('mesas.detalhes');

    // Conta da mesa (comanda)
    Route::post('/mesas/{id}/conta/abater', [MesaController::class, 'abaterItensContaMesa'])->name('mesas.conta.abater');
    Route::post('/mesas/{id}/conta/item/{itemId}/atualizar', [MesaController::class, 'atualizarItemContaMesa'])->name('mesas.conta.item.atualizar');
    Route::post('/mesas/{id}/conta/item/{itemId}/remover', [MesaController::class, 'removerItemContaMesa'])->name('mesas.conta.item.remover');
});


Route::post('/garcom/adicionar-produto', [GarcomController::class, 'adicionarProduto'])
    ->name('garcom.adicionar-produto');

///////////////////////////////////////////////////////
/* ACESSO NEGADO */
///////////////////////////////////////////////////////
Route::get('/AcessoNegado', function () {
    return view('Admin.PermisaoNegada');
})->name('AcessoNegado');







///////////////////////////////////////////////////////
/* GERENCIAMENTO DE FUNCIONARIO */
///////////////////////////////////////////////////////

Route::middleware(['auth', 'admin.access', 'SecureKey.role:' . Roles::GERENCIAMENTO_FUNCIONARIOS])->group(function () {
    Route::get('/gerenciamento_Funcionario', [GerenciamentoUsuarioController::class, 'gerenciamentoFuncionario'])
        ->name('gerenciamento_funcionarios');

    Route::post('/funcionarios/{id}/atualizar', [GerenciamentoUsuarioController::class, 'atualizarFuncionario'])
        ->name('funcionarios.atualizar');


    Route::post('/funcionarios/{id}/deletar', [GerenciamentoUsuarioController::class, 'deletarUsuario'])
        ->name('funcionarios.deletar');


Route::post('/atualizar_produto/{id}/atualizar', [GerenciamentoProdutoController::class, 'atualizarProduto'])
    ->middleware('SecureKey.role:' . Roles::GERENCIAMENTO_PRODUTOS)
    ->name('produtos.atualizar');
});
//pegar dados do usuário logado




///////////////////////////////////////////////////////
/* GERENCIAMENTO DE AUTENTICAÇÃO */
///////////////////////////////////////////////////////
Route::post('/registro', [RegisterController::class, 'register'])->name('registro');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/esqueci-senha', [LoginController::class, 'recuperarSenha'])->name('senha.recuperar');
Route::post('/redefinir-senha', [LoginController::class, 'atualizarSenha'])->name('senha.atualizar');
Route::post('/Alterar_Dados', [AdminController::class, 'AlterarDados'])->name('Alterar_Dados')->middleware('auth');





Route::post('/', [IndexController::class, 'index'])->name('index');




///////////////////////////////////////////////////////
/* GERENCIAMENTO DE AUTENTICAÇÃO cypress*/
///////////////////////////////////////////////////////


Route::post('/cypress-login-admin', function () {
    Auth::loginUsingId(1); // ID do admin no banco
    return response()->json(['ok' => true]);
});
