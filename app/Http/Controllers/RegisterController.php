<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\GenericBase;
use Illuminate\Http\Request;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;

class RegisterController extends Controller
{
    protected AuthService $authService;
    protected GenericBase $genericBase;

    public function __construct(AuthService $authService, GenericBase $genericBase)
    {
        $this->authService = $authService;
        $this->genericBase = $genericBase;
    }


    public function registerFuncionario(Request $request)
    {

        $data = $request->only('nome', 'email', 'senha','senha_confirmation','telefone','tipo_usuario_id','has_ativo');

        $salarioBruto = $request->input('salario');
        $data['salario'] = $this->genericBase->normalizarMoeda($salarioBruto);

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
        $data = $request->only(
            'nome',
            'email',
            'senha',
            'senha_confirmation',
            'telefone'
        );

        $data['tipo_usuario_id'] = 1;

        $resultado = $this->authService->register($data);

        if ($resultado !== PassMensagens::CADASTRO_REALIZADO) {

            return redirect()
                ->route('registro.form')
                ->with('erro', $resultado)
                ->withInput();
        }

        return redirect()
            ->route('login')
            ->with('sucesso', $resultado);
    }
}
