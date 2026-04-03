<?php

namespace App\Services;

use App\Mensagens\ErroMensagens;
use App\Models\UsuarioModel;
use App\Repositoryimpl\GenericBaseRepositoryimpl;
use Illuminate\Support\Facades\Auth;

class GenericBase
{
    public function __construct(private GenericBaseRepositoryimpl $repository)
    {
    }

    public function pegarUsuarioEmail($data){
        $usuario = $this->repository->pegarUsuarioEmail($data);
        if (!$usuario) {
           $usuario = null;
        }
        return $usuario;
    }

    public function existeFuncionario($usuarioId)
    {
        return $this->repository->existeFuncionario($usuarioId);
    }

    public function formatName($name)
    {
        return $name = $name?->nome ? explode(' ', trim($name->nome))[0] : 'Usuário';
    }

    public function hasLogado()
    {
        $usuarioLogado = $this->pegarUsuarioLogado();

        return $usuarioLogado;
    }
    public function findById(int $id)
    {
        return $this->repository->findById($id);
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

        return $this->repository->findByProdutos($categorias);
    }

    public function pegarProdutos()
    {
        return $this->repository->pegarProdutos();
    }

    public function findAll()
    {
        return $this->repository->findAll();
    }

    public function findByCidade()
    {
        return $this->repository->findByCidade();
    }

    public function findFuncionarios()
    {
        return $this->repository->findFuncionarios();
    }

    public function alterar(UsuarioModel $usuario, array $dados)
    {
        $usuario->update($dados);
        return $usuario;
    }

    public function gerarNumero()
    {
        return random_int(1, 1000);
    }

    public function deletar(UsuarioModel $usuario)
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

     public function logout()
    {
        Auth::logout();
        session()->forget('usuario_logado');
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('home');
    }


    public function deleteFuncionarioEUsuario(int $usuarioId): bool
    {
        return $this->repository->deleteFuncionarioEUsuario($usuarioId);
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
        if ($usuario instanceof UsuarioModel) {
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
        return $this->repository->pegarItensCarrinho((int) $usuarioId);
    }

    public function findByProdutosIsUsuario($produtoId, $usuarioId)
    {
        return $this->repository->findByProdutosIsUsuario((int) $produtoId, (int) $usuarioId);
    }
}
