<?php

namespace App\Services;

use App\Mensagens\ErroMensagens;
use App\Models\Usuario;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\Carrinho;
use App\Models\Cidade;

class GenericBase
{

    public function hasLogado()
    {
        $usuarioLogado = $this->pegarUsuarioLogado();

        return $usuarioLogado;
    }
    public function findById(int $id)
    {
        return Usuario::find($id);
    }

    public function findByProdutos($categoria)
    {
        $categorias = is_array($categoria) ? $categoria : [$categoria];

        // Normaliza variações comuns de "Porções" para evitar lista vazia por diferença de acento/plural.
        if (in_array('Porcao', $categorias, true) || in_array('Porcao', array_map('strval', $categorias), true)) {
            $categorias[] = 'Porções';
            $categorias[] = 'Porção';
            $categorias[] = 'Porcoes';
        }

        return Produto::query()
            ->where('disponivel', true)
            ->whereHas('categoria', function ($query) use ($categorias) {
                $query->whereIn('nome', $categorias)
                    ->where('deleted', false);
            })
            ->get();
    }

    public function pegarProdutos()
    {
        return Produto::where('disponivel', true)->get();
    }

    public function findAll()
    {
        return Usuario::all();
    }

    public function findByCidade()
    {
        return Cidade::orderBy('nome')->get();
    }

    public function findFuncionarios()
    {
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

        // Normaliza para manter compatibilidade: alguns pontos do projeto
        // acessam como objeto ($usuario->nome) e outros como array ($usuario['tipo']).
        if ($usuario instanceof Usuario) {
            $primeiroNome = '';
            if (!empty($usuario->nome)) {
                $primeiroNome = explode(' ', trim((string) $usuario->nome))[0] ?? '';
            }

            // Atributos auxiliares (não persistidos no banco)
            $usuario->setAttribute('primeiro_nome', $primeiroNome);
            $usuario->setAttribute('tipo', $usuario->tipo_descricao ?? null);
            $usuario->setAttribute('tipo_id', $usuario->tipo_usuario_id ?? null);
        }

        return $usuario;
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


    public function pegarItensCarrinho($usuarioId)
    {
        return Carrinho::with('produto')
            ->where('usuario_id', $usuarioId)
            ->get();
    }

    public function findByProdutosIsUsuario($produtoId, $usuarioId)
    {
        return Carrinho::where('produto_id', $produtoId)
            ->where('usuario_id', $usuarioId)
            ->first();
    }
}
