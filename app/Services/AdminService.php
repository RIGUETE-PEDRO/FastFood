<?php

namespace App\Services;

use App\Models\Funcionario;

class AdminService
{
    protected GenericBase $genericBase;

    public function __construct()
    {
        $this->genericBase = new GenericBase();
    }

    public function InserirImagemPerfil()
    {
     $user = session('usuario_logado');

        $data = request()->only('nome', 'email', 'telefone');

        $usuario = $this->genericBase->findById($user->id);

        if (!$usuario) {
            return redirect()->route('perfil')->with('erro', 'Usuário não encontrado.');
        }

        $usuario->nome = $data['nome'];
        $usuario->email = $data['email'];
        $usuario->telefone = $data['telefone'];

        // Upload da imagem
        if (request()->hasFile('url_imagem_perfil')) {

            request()->validate([
                'url_imagem_perfil' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $file = request()->file('url_imagem_perfil');

            // Apagar imagem antiga
            if (
                $usuario->url_imagem_perfil &&
                file_exists(public_path('img/perfil/' . $usuario->url_imagem_perfil))
            ) {
                unlink(public_path('img/perfil/' . $usuario->url_imagem_perfil));
            }

            $fileName = 'perfil_' . $usuario->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('img/perfil'), $fileName);

            // SALVA NA COLUNA CORRETA
            $usuario->url_imagem_perfil = $fileName;
        }

        $usuario->save();

        session(['usuario_logado' => $usuario]);

        return redirect()->route('perfil')->with('sucesso', 'Dados atualizados com sucesso.');
    }

    public function buscarFuncionarios($searchTerm)
    {
        $query = Funcionario::with('usuario');

        if (!empty($searchTerm)) {
            $query->whereHas('usuario', function ($q) use ($searchTerm) {
                $q->where('nome', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        return $query->get();
    }


    public function listarFuncionarios()
    {
        // Buscar apenas usuários que NÃO sejam clientes (tipo_usuario_id != 1)
        // Ou seja, buscar: Estabelecimento, Administrador e Entregador
        return $this->genericBase->findFuncionarios();
    }

}
