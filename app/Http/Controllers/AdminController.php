<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;

class AdminController extends Controller
{
    protected GenericBase $genericBase;

    public function __construct(GenericBase $genericBase)
    {
        $this->genericBase = $genericBase;
    }

    public function InfoPerfil()
    {
        $user = session('usuario_logado');

        if (!$user) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login primeiro.');
        }

        return view('Admin.perfil', [
            'usuario' => $user,
            'tipoUsuario' => $this->mapearTipoUsuario($user->tipo_usuario_id ?? null),
        ]);
    }

    public function nomeUsuario()
    {
        $user = session('usuario_logado');

        if (!$user) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login primeiro.');
        }

        return view('Admin.Administrativo', [
            'usuario' => $user,
            'nomeUsuario' => $user->nome,
            'tipoUsuario' => $this->mapearTipoUsuario($user->tipo_usuario_id ?? null),
        ]);
    }

        public function AlterarDados()
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


    private function mapearTipoUsuario(?int $tipoId): string
    {
        $tipos = [
            1 => 'Administrador',
            2 => 'Funcionário',
            3 => 'Cliente',
            4 => 'Entregador',
        ];

        return $tipos[$tipoId] ?? 'Usuário';
    }
}
