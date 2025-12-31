<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GerenciamentoUsuarioController;
use App\Http\Controllers\GerenciamentoProdutoController;

//rota da página inicial
Route::get('/', function () {
    return view('Index');
})->name('home');

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
Route::get('/Administrativo', [AdminController::class, 'nomeUsuario'])->name('Administrativo');

Route::get('/gerenciamento_Funcionario', [GerenciamentoUsuarioController::class, 'gerenciamentoFuncionario'])->name('gerenciamento_funcionarios');

//gerenciamento de produtos
Route::post('/gerenciamento_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])->name('gerenciamento_produtos');

//logout
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

//perfil
Route::get('/perfil', [AdminController::class, 'InfoPerfil'])->name('perfil');

//buscar funcionários
Route::get('/funcionarios/buscar', [GerenciamentoUsuarioController::class, 'buscarFuncionarios'])->name('funcionarios.buscar');

//atualizar funcionário
Route::post('/funcionarios/{id}/atualizar', [GerenciamentoUsuarioController::class, 'atualizarFuncionario'])->name('funcionarios.atualizar');

Route::post('/funcionarios/{id}/deletar', [GerenciamentoUsuarioController::class, 'deletarUsuario'])->name('funcionarios.deletar');

//pegar dados do usuário logado
Route::post('/perfil', [AdminController::class, 'InfoPerfil'])->name('usuario');


//rotas de autenticação
Route::post('/registro', [RegisterController::class, 'register'])->name('registro');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/esqueci-senha', [LoginController::class, 'recuperarSenha'])->name('senha.recuperar');
Route::post('/redefinir-senha', [LoginController::class, 'atualizarSenha'])->name('senha.atualizar');
Route::post('/Alterar_Dados', [AdminController::class, 'AlterarDados'])->name('Alterar_Dados');

//rota de cadastro de produto
Route::post('/Cadastrar_Produto', [GerenciamentoProdutoController::class, 'cadastrarProduto'])->name('Cadastrar_Produto');


Route::get('/gerenciamento_Produtos', [GerenciamentoProdutoController::class, 'gerenciamentoProduto'])
    ->name('gerenciamento_Produtos');

    Route::post('/gerenciamento_Produtos/{id}/deletar', [GerenciamentoProdutoController::class, 'deletarProduto'])->name('deletar_produto');
