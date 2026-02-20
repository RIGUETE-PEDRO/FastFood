<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\GenericBase;
use Illuminate\Http\Request;
use App\Mensagens\ErroMensagens;


class RegisterController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    public function registerFuncionario(Request $request)
    {
        $genericBase = new GenericBase();
        $data = $request->only('nome', 'email', 'senha','senha_confirmation','telefone','tipo_usuario_id','has_ativo');

        $salarioBruto = $request->input('salario');
        $data['salario'] = $genericBase->normalizarMoeda($salarioBruto);

        $usuario = $this->authService->register($data);

        if (!$usuario) {
            return redirect()->back()
                ->with('erro', ErroMensagens::ERRO_REGISTRAR_FUNCIONARIO)
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
                ->with('erro', ErroMensagens::ERRO_REGISTRAR_USUARIO)
                ->withInput();
        }

        return redirect()->route('registro');
    }
}
