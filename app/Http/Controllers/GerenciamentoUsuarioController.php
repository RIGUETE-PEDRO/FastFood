<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;

use App\Services\AdminService;

use Illuminate\Http\Request;

class GerenciamentoUsuarioController extends Controller
{


    public function buscarFuncionarios(Request $request)
    {
        $searchTerm = $request->input('search');

        $adminService = new AdminService();
        $lista = $adminService->buscarFuncionarios($searchTerm);

        $usuario = session('usuario_logado');
        $nomeUsuarioLogado = $usuario->nome ?? 'Usuário';

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



        $nomeUsuarioLogado = $usuario->nome ?? 'Usuário';

        return view('Admin.GerenciamentoFuncionario', [
            'lista' => $lista,
            'usuario' => $usuario,
            'nomeUsuario' => $nomeUsuarioLogado
        ]);
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

}

