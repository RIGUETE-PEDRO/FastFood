<?php

use Illuminate\Support\Facades\Route;
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


///////////////////////////////////////////////////////
/* GERENCIAMENTO DE PERFIL */
///////////////////////////////////////////////////////

    Route::get('/', [IndexController::class, 'index'])->name('home');

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
        ->middleware(['auth', 'admin.access']);

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
    Route::get('/funcionarios/buscar', [GerenciamentoUsuarioController::class, 'buscarFuncionarios'])->name('funcionarios.buscar');
/////////////////////////////////////////////////////////


///////////////////////////////////////////////////////
/* PROCESSO DOS PEDIDOS */
///////////////////////////////////////////////////////
//administrativo
Route::get('/Administrativo', [AdminController::class, 'nomeUsuario'])
    ->name('Administrativo')
    ->middleware(['auth', 'admin.access']);



Route::get('/Pedidos.Administrativo', [PedidosFeitosController::class, 'verPedidosAdmin'])
    ->name('Pedidos.Administrativo')
    ->middleware(['auth', 'admin.access']);

Route::patch('/Pedidos.Administrativo/{pedido}/status', [PedidosFeitosController::class, 'atualizarStatus'])
    ->name('Pedidos.StatusAtualizar')
    ->middleware(['auth', 'admin.access']);

Route::post('/Pedidos.Administrativo/{pedido}/avancar', [PedidosFeitosController::class, 'avancarStatus'])
    ->name('Pedidos.StatusAvancar')
    ->middleware(['auth', 'admin.access']);

Route::get('/pedidos', [PedidoController::class, 'verPedido'])->name('pedidos')->middleware('auth');


///////////////////////////////////////////////////////
/* GERENCIAMENTO DE PRODUTOS */
///////////////////////////////////////////////////////
Route::post('/gerenciamento_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])
    ->name('gerenciamento_produtos')
    ->middleware(['auth', 'admin.access']);

Route::post('/Cadastrar_Produto', [GerenciamentoProdutoController::class, 'cadastrarProduto'])
    ->name('Cadastrar_Produto')
    ->middleware(['auth', 'admin.access']);

 Route::get('/gerenciamento_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])
    ->name('gerenciamento_Produtos')
    ->middleware(['auth', 'admin.access']);

Route::post('/gerenciamento_Produtos/{id}/deletar', [GerenciamentoProdutoController::class, 'deletarProduto'])
    ->name('deletar_produto')
    ->middleware(['auth', 'admin.access']);




///////////////////////////////////////////////////////
/* GERENCIAMENTO DO USUARIO */
///////////////////////////////////////////////////////
Route::get('/Lanches',[LanchesController::class, 'Lanches'])->name('Lanches');

Route::get('/Pizza', [PizzaController::class, 'Pizza'])->name('Pizza');

Route::get('/Bebidas', [BebidasController::class, 'Bebidas'])->name('Bebidas');

Route::get('/porcao', [PorcaoController::class, 'porcao'])->name('Porcao');


///////////////////////////////////////////////////////
/* GERENCIAMENTO DE CARRINHO */
///////////////////////////////////////////////////////
Route::get('/carrinho', [CarrinhoController::class, 'verCarrinho'])->name('carrinho')->middleware('auth');

Route::post('/carrinho/adicionar', [CarrinhoController::class, 'adicionarAoCarrinho'])->name('carrinho.adicionar')->middleware('auth');

Route::post('/carrinho/endereco', [CarrinhoController::class, 'pegarEndereco'])->name('carrinho.endereco')->middleware('auth');
Route::post('/carrinho/pagamento', [CarrinhoController::class, 'registrarPedido'])->name('carrinho.Pedido')->middleware('auth');

Route::post('/carrinho/selecionarCidade', [CarrinhoController::class, 'selecionarCidade'])->name('cidade.buscar')->middleware('auth');


Route::post('/carrinho/{id}/remover', [CarrinhoController::class, 'removerDoCarrinho'])->name('carrinho.remover')->middleware('auth');

Route::put('/carrinho/{id}/atualizarQuantidade', [CarrinhoController::class, 'atualizarQuantidade'])->name('carrinho.atualizarQuantidade')->middleware('auth');

Route::put('/carrinho/{id}/selecionar', [CarrinhoController::class, 'toggleSelecionar'])->name('carrinho.toggle')->middleware('auth');

Route::get('/carrinho/{id}/deletar', function ($id) {
    return redirect()->route('carrinho');
})->middleware('auth');

Route::match(['post', 'delete'], '/carrinho/{id}/deletar', [CarrinhoController::class, 'deletarEndereco'])->name('endereco.excluir')->middleware('auth');







///////////////////////////////////////////////////////
/* ACESSO NEGADO */
///////////////////////////////////////////////////////
Route::get('/AcessoNegado', function () {
    return view('Admin.PermisaoNegada');
})->name('AcessoNegado');







///////////////////////////////////////////////////////
/* GERENCIAMENTO DE FUNCIONARIO */
///////////////////////////////////////////////////////

Route::get('/gerenciamento_Funcionario', [GerenciamentoUsuarioController::class, 'gerenciamentoFuncionario'])
    ->name('gerenciamento_funcionarios')
    ->middleware(['auth', 'admin.access']);

Route::post('/funcionarios/{id}/atualizar', [GerenciamentoUsuarioController::class, 'atualizarFuncionario'])
    ->name('funcionarios.atualizar')
    ->middleware(['auth', 'admin.access']);

Route::post('/funcionarios/{id}/deletar', [GerenciamentoUsuarioController::class, 'deletarUsuario'])
    ->name('funcionarios.deletar')
    ->middleware(['auth', 'admin.access']);

Route::post('/atualizar_produto/{id}/atualizar', [GerenciamentoProdutoController::class, 'atualizarProduto'])
    ->name('produtos.atualizar')
    ->middleware(['auth', 'admin.access']);

//pegar dados do usuário logado




///////////////////////////////////////////////////////
/* GERENCIAMENTO DE AUTENTICAÇÃO */
///////////////////////////////////////////////////////
Route::post('/registro', [RegisterController::class, 'register'])->name('registro');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/esqueci-senha', [LoginController::class, 'recuperarSenha'])->name('senha.recuperar');
Route::post('/redefinir-senha', [LoginController::class, 'atualizarSenha'])->name('senha.atualizar');
Route::post('/Alterar_Dados', [AdminController::class, 'AlterarDados'])->name('Alterar_Dados');





Route::post('/', [IndexController::class, 'index'])->name('index');
