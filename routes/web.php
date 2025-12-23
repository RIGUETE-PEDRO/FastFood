<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GerenciamentoUsuarioController;

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

//logout
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

//perfil
Route::get('/perfil', [AdminController::class, 'InfoPerfil'])->name('perfil');


//pegar dados do usuário logado
Route::post('/perfil', [AdminController::class, 'InfoPerfil'])->name('usuario');

//rotas de autenticação
Route::post('/registro', [RegisterController::class, 'register'])->name('registro');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/esqueci-senha', [LoginController::class, 'recuperarSenha'])->name('senha.recuperar');
Route::post('/redefinir-senha', [LoginController::class, 'atualizarSenha'])->name('senha.atualizar');

Route::post('/administrativo', [AdminController::class, 'nomeUsuario'])->name('nomeUsuario');

Route::post('/Alterar_Dados', [AdminController::class, 'AlterarDados'])->name('Alterar_Dados');

