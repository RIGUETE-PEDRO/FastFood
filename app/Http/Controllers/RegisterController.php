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


    public function registerFuncionario(Request $request)
    {
        $data = $request->only('nome', 'email', 'senha','senha_confirmation','telefone','tipo_usuario_id','salario','has_ativo');

        $usuario = $this->authService->register($data);

        if (!$usuario) {
            return redirect()->back()
                ->with('erro', 'Erro ao registrar funcionário')
                ->withInput();
        }

        return redirect()->route('gerenciamento_funcionarios');
    }



    //registrar usuário
    public function register(Request $request)
    {
        $data = $request->only('nome', 'email', 'senha','senha_confirmation','telefone');
        $data['tipo_usuario_id'] = 1;
        $usuario = $this->authService->register($data);

        if (!$usuario) {
            return redirect()->back()
                ->with('erro', 'Erro ao registrar usuário')
                ->withInput();
        }

        return redirect()->route('registro');
    }
}
