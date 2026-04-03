<?php

namespace App\Http\Controllers;

use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Services\GenericBase;
use App\Services\GerenciamentoFuncionarioService;
use App\Services\AdminService;

use App\Models\Usuario;
use App\Models\Funcionario;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class GerenciamentoUsuarioController extends Controller
{

    protected GenericBase $genericBase;
    protected AdminService $adminService;
    protected GerenciamentoFuncionarioService $gerenciamentoFuncionarioService;

    public function __construct(GenericBase $genericBase, AdminService $adminService, GerenciamentoFuncionarioService $gerenciamentoFuncionarioService)
    {
        $this->genericBase = $genericBase;
        $this->adminService = $adminService;
        $this->gerenciamentoFuncionarioService = $gerenciamentoFuncionarioService;
    }

    public function buscarFuncionarios(Request $request)
    {
        $searchTerm = $request->input('search');

        $lista = $this->adminService->buscarFuncionarios($searchTerm);
        $usuarioLogado =  $this->genericBase->hasLogado();
        $nomeUsuarioLogado = $this->genericBase->formatName($usuarioLogado);

        return view('Admin.GerenciamentoFuncionario', [
            'lista' => $lista,
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $nomeUsuarioLogado
        ]);
    }


    public function gerenciamentoFuncionario()
    {

        $lista = $this->adminService->listarFuncionarios();
        $usuarioLogado =  $this->genericBase->hasLogado();
        $primeiroNome = $this->genericBase->formatName($usuarioLogado);

        return view('Admin.GerenciamentoFuncionario', [
            'lista' => $lista,
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $primeiroNome
        ]);
    }


    public function deletarUsuario($id)
    {

        $usuario = $this->genericBase->findById($id);

        if (!$usuario) {
            return  redirect()->route('gerenciamento_funcionarios')
                ->with('erro', ErroMensagens::USUARIO_NAO_ENCONTRADO);
        }

        $ok = $this->genericBase->deleteFuncionarioEUsuario($id);

        if (!$ok) {
            return redirect()->route('gerenciamento_funcionarios')
                ->with('erro', ErroMensagens::ERRO_DELETAR_USUARIO);
        }

        return redirect()->route('gerenciamento_funcionarios')
            ->with('sucesso', PassMensagens::DELETE_SUCESSO);
    }


    public function alteraUsuario($id)
    {
        $usuario = $this->genericBase->findById($id);

        if (!$usuario) {
            return redirect()->route('gerenciamento.usuarios')
                ->with('erro', ErroMensagens::USUARIO_NAO_ENCONTRADO);
        }

        return view('Admin.alterar_usuario', [
            'usuario' => $usuario,
        ]);
    }


    public function atualizarFuncionario(Request $request, $id)
    {
        $this->gerenciamentoFuncionarioService->atualizarFuncionario($request, $id);
        
        return redirect()->route('gerenciamento_funcionarios')->with('sucesso', PassMensagens::ATUALIZAR_FUNCIONARIO_SUCESSO);
    }
}
