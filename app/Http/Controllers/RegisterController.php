<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {


        $data = $request->only('nome', 'email', 'senha','senha_confirmation','telefone');

        $usuario = $this->authService->register($data);

        if (!$usuario) {
            return redirect()->back()
                ->with('erro', 'Erro ao registrar usuário')
                ->withInput();
        }
        
        return redirect()->route('registro');
    }
}
