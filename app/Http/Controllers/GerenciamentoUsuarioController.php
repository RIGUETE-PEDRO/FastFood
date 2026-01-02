<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;

use App\Services\AdminService;

use App\Models\Usuario;
use App\Models\Funcionario;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class GerenciamentoUsuarioController extends Controller
{


    public function buscarFuncionarios(Request $request)
    {
        $searchTerm = $request->input('search');

        $adminService = new AdminService();
        $lista = $adminService->buscarFuncionarios($searchTerm);

        $usuario = session('usuario_logado');
        $nomeUsuarioLogado = $usuario?->nome ? explode(' ', trim($usuario->nome))[0] : 'Usuário';

        return view('Admin.GerenciamentoFuncionario', [
            'lista' => $lista,
            'usuario' => $usuario,
            'nomeUsuario' => $nomeUsuarioLogado
        ]);
    }


    public function gerenciamentoFuncionario()
    {
        $adminService = new AdminService();
        $lista = $adminService->listarFuncionarios();

        $usuario = session('usuario_logado');

        $primeiroNome = explode(' ', trim($usuario->nome))[0];

        return view('Admin.GerenciamentoFuncionario', [
            'lista' => $lista,
            'usuario' => $usuario,
            'nomeUsuario' => $primeiroNome
        ]);
    }


    public function deletarUsuario($id)
    {
        $genericBase = new GenericBase();
        $usuario = $genericBase->findById($id);

        if (!$usuario) {
            return redirect()->route('gerenciamento_funcionarios')
                ->with('erro', 'Usuário não encontrado');
        }

        $ok = $genericBase->deleteFuncionarioEUsuario($id);

        if (!$ok) {
            return redirect()->route('gerenciamento_funcionarios')
                ->with('erro', 'Não foi possível excluir o usuário');
        }

        return redirect()->route('gerenciamento_funcionarios')
            ->with('sucesso', 'Usuário deletado com sucesso');
    }


    public function alteraUsuario($id)
    {
        $genericBase = new GenericBase();
        $usuario = $genericBase->findById($id);

        if (!$usuario) {
            return redirect()->route('gerenciamento.usuarios')
                ->with('erro', 'Usuário não encontrado');
        }

        return view('Admin.alterar_usuario', [
            'usuario' => $usuario,
        ]);
    }


    public function atualizarFuncionario(Request $request, $id)
    {
        // Normaliza salário para formato numérico (aceita entrada com pontos, vírgulas ou "R$")
        $genericBase = new GenericBase();

        $salarioBruto = $request->input('salario');
        $salarioNormalizado = $genericBase->normalizarMoeda($salarioBruto);
        if ($salarioBruto !== null) {
            $request->merge(['salario' => $salarioNormalizado]);
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'nullable|string|max:50',
            'tipo_usuario_id' => 'required|integer|in:2,3,4',
            'has_ativo' => 'nullable|boolean',
            'senha' => 'nullable|min:6|confirmed',
            'salario' => 'nullable|numeric|min:0',
        ]);

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return redirect()->back()->with('erro', 'Usuário não encontrado');
        }

        // Atualiza dados do usuário
        $usuario->nome = $request->input('nome');
        $usuario->email = $request->input('email');
        $usuario->telefone = $request->input('telefone');
        $usuario->tipo_usuario_id = $request->input('tipo_usuario_id');

        // Atualiza senha apenas se enviada
        if ($request->filled('senha')) {
            $usuario->senha = Hash::make($request->input('senha'));
        }

        $usuario->save();

        // Atualiza dados do funcionário relacionado
        $funcionario = Funcionario::where('usuario_id', $usuario->id)->first();

        if ($funcionario) {
            $funcionario->has_ativo = $request->boolean('has_ativo', true);
            $funcionario->salario = $request->input('salario');
            $funcionario->save();
        }

        return redirect()->route('gerenciamento_funcionarios')->with('sucesso', 'Funcionário atualizado com sucesso');
    }



}

