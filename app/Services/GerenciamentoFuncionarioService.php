<?php

namespace App\Services;

use App\Mensagens\ErroMensagens;
use App\Models\Funcionario;
use Illuminate\Support\Facades\Hash;

class GerenciamentoFuncionarioService{

        protected GenericBase $genericBase;

        public function __construct(GenericBase $genericBase)
        {
            $this->genericBase = $genericBase;
        }


    public function atualizarFuncionario($request, $id){
        $salarioBruto = $request->input('salario');
        $salarioNormalizado = $this->genericBase->normalizarMoeda($salarioBruto);

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

        $usuario = $this->genericBase->findById($id);
        if (!$usuario) {
            return redirect()->back()->with('erro', ErroMensagens::USUARIO_NAO_ENCONTRADO);
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

    }

}
