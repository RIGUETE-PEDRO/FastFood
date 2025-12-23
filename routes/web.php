<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return view('Index');
})->name('home');

Route::get('/login', function () {
    return view('Login');
})->name('login.form');

Route::get('/registro', function () {
    return view('Cadastrar');
})->name('registro.form');

Route::get('/esqueci-senha', function () {
    return view('Esqueci-senha');
})->name('senha.form');

Route::get('/redefinir-senha', function () {
    return view('Redefinir-senha');
})->name('senha.redefinir.form');

Route::get('/administrativo', function () {
    return view('Admin.Administrativo');
})->name('Administrativo');


Route::post('/registro', [RegisterController::class, 'register'])->name('registro');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/esqueci-senha', [LoginController::class, 'recuperarSenha'])->name('senha.recuperar');
Route::post('/redefinir-senha', [LoginController::class, 'atualizarSenha'])->name('senha.atualizar');

