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
use App\Http\Controllers\ProdutosController;

//rota da página inicial
Route::get('/', [IndexController::class, 'index'])->name('home');

//login
Route::get('/login', function () {
    return view('Login');
})->name('login.form');

//cadastro
Route::get('/registro', function () {
    return view('Cadastrar');
})->name('registro.form');

Route::get('/Lista_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])->name('ListaProdutos');
//cadastro de funcionário
Route::post('/CadastrarFuncionario', [RegisterController::class, 'registerFuncionario'])->name('CadastrarFuncionario');

//esqueci minha senha
Route::get('/esqueci-senha', function () {
    return view('Esqueci-senha');
})->name('senha.form');

//redifinir senha
Route::get('/redefinir-senha', function () {
    return view('Redefinir-senha');
})->name('senha.redefinir.form');

//administrativo
Route::get('/Administrativo', [AdminController::class, 'nomeUsuario'])->name('Administrativo')->middleware('auth');

Route::get('/gerenciamento_Funcionario', [GerenciamentoUsuarioController::class, 'gerenciamentoFuncionario'])->name('gerenciamento_funcionarios')->middleware('auth');

//gerenciamento de produtos
Route::post('/gerenciamento_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])->name('gerenciamento_produtos')->middleware('auth');

//logout
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

//perfil
Route::get('/perfil', [AdminController::class, 'InfoPerfil'])->name('perfil')->middleware('auth');

//buscar funcionários
Route::get('/funcionarios/buscar', [GerenciamentoUsuarioController::class, 'buscarFuncionarios'])->name('funcionarios.buscar');

Route::get('/Lanches',[LanchesController::class, 'Lanches'])->name('Lanches');

Route::get('/Pizza', [PizzaController::class, 'Pizza'])->name('Pizza');

Route::get('/Bebidas', [BebidasController::class, 'Bebidas'])->name('Bebidas');

Route::get('/porcao', [PorcaoController::class, 'porcao'])->name('Porcao');

////////////////////////////////////////////////////////////////////////////////////////
Route::get('/carrinho', [ProdutosController::class, 'verCarrinho'])->name('carrinho')->middleware('auth');



//Rotas POST
Route::post('/carrinho/adicionar', [ProdutosController::class, 'adicionarAoCarrinho'])->name('carrinho.adicionar')->middleware('auth');

Route::post('/carrinho/{id}/remover', [ProdutosController::class, 'removerDoCarrinho'])->name('carrinho.remover')->middleware('auth');

Route::put('/carrinho/{id}/atualizarQuantidade', [ProdutosController::class, 'atualizarQuantidade'])->name('carrinho.atualizarQuantidade')->middleware('auth');


//atualizar funcionário
Route::post('/funcionarios/{id}/atualizar', [GerenciamentoUsuarioController::class, 'atualizarFuncionario'])->name('funcionarios.atualizar')->middleware('auth');

Route::post('/funcionarios/{id}/deletar', [GerenciamentoUsuarioController::class, 'deletarUsuario'])->name('funcionarios.deletar')->middleware('auth');

Route::post('/atualizar_produto/{id}/atualizar', [GerenciamentoProdutoController::class, 'atualizarProduto'])->name('produtos.atualizar')->middleware('auth');

//pegar dados do usuário logado
Route::post('/perfil', [AdminController::class, 'InfoPerfil'])->name('usuario')->middleware('auth');


//rotas de autenticação
Route::post('/registro', [RegisterController::class, 'register'])->name('registro');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/esqueci-senha', [LoginController::class, 'recuperarSenha'])->name('senha.recuperar');
Route::post('/redefinir-senha', [LoginController::class, 'atualizarSenha'])->name('senha.atualizar');
Route::post('/Alterar_Dados', [AdminController::class, 'AlterarDados'])->name('Alterar_Dados');

//rota de cadastro de produto
Route::post('/Cadastrar_Produto', [GerenciamentoProdutoController::class, 'cadastrarProduto'])->name('Cadastrar_Produto');


Route::get('/gerenciamento_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])
    ->name('gerenciamento_Produtos')->middleware('auth');

Route::post('/gerenciamento_Produtos/{id}/deletar', [GerenciamentoProdutoController::class, 'deletarProduto'])->name('deletar_produto')->middleware('auth');

Route::post('/', [IndexController::class, 'index'])->name('index');
