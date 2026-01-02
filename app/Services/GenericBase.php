<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Funcionario;
use App\Models\Produto;

class GenericBase
{

    public function findById(int $id)
    {
        return Usuario::find($id);
    }

    public function findByProdutos($categoria)
    {
        return Produto::whereHas('categoria', function ($query) use ($categoria) {
            $query->where('nome', $categoria)->where('disponivel', true);
        })->get();
    }

    public function pegarProdutos()
    {
        return Produto::where('disponivel', true)->get();
    }

    public function findAll()
    {
        return Usuario::all();
    }

    public function findFuncionarios()
    {
        // Busca da tabela funcionario com o relacionamento usuario
        return Funcionario::with('usuario')->get();
    }

    public function alterar(Usuario $usuario, array $dados)
    {
        $usuario->update($dados);
        return $usuario;
    }

    public function gerarNumero()
    {
        return random_int(1, 1000);
    }

    public function deletar(Usuario $usuario)
    {
        return $usuario->delete();
    }


    public function listagem($rota)
    {

        $lista = $this->findAll();

        return view($rota, [
            'lista' => $lista,
        ]);
    }


    public function delete($id)
    {
        $usuario = $this->findById($id);
        return $usuario ? $usuario->delete() : false;
    }



    public function deleteFuncionarioEUsuario(int $usuarioId): bool
    {
        $funcionario = Funcionario::where('usuario_id', $usuarioId)->first();

        // Exclui o funcionário primeiro para não violar FK
        if ($funcionario) {
            $funcionario->delete();
        }

        $usuario = $this->findById($usuarioId);
        if ($usuario) {
            return (bool) $usuario->delete();
        }

        return false;
    }


    public function pegarUsuarioLogado()
    {
        $usuario = session('usuario_logado');
        // Se não tiver sessão, considera deslogado.
        if (!$usuario) {
            return null;
        }

        return [
            'id' => $usuario->id ?? null,
            'nome' => explode(' ', trim($usuario->nome))[0],
            'tipo' => $usuario->tipo_descricao ?? null, // accessor do Model
            'tipo_id' => $usuario->tipo_usuario_id ?? null,
            'url_imagem_perfil' => $usuario->url_imagem_perfil ?? null,
        ];
    }


    public function normalizarMoeda(?string $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        // Remove tudo que não for número, vírgula ou ponto
        $limpo = preg_replace('/[^0-9,\.]/', '', $valor);

        // Se tiver vírgula como separador decimal, troca por ponto
        // Remove separadores de milhar
        if (str_contains($limpo, ',')) {
            $limpo = str_replace('.', '', $limpo);
            $limpo = str_replace(',', '.', $limpo);
        }

        return is_numeric($limpo) ? (float) $limpo : null;
    }
}
