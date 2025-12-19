<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data)
    {
        if (Usuario::where('email', $data['email'])->exists()) {
            return redirect()->back()->with('erro', 'esse E-mail já encontra cadastrado.');
        }

        if ($data['senha'] !== $data['senha_confirmation']) {
            return redirect()->back()->with('erro', 'As senhas não coincidem.');
        }

        if (strlen($data['senha']) < 6) {
            return redirect()->back()->with('erro', 'A senha deve ter pelo menos 6 caracteres.');
        }


        Usuario::create([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => bcrypt($data['senha']),
            'telefone' => $data['telefone'],
            'tipo_usuario_id' => 1,
        ]);

        return redirect()->back()
            ->with('sucesso', 'Cadastro realizado com sucesso!');
    }

    public function login(array $credentials): ?Usuario
    {
        $usuario = Usuario::where('email', $credentials['email'])->first();

        if (!$usuario) {
            return null;
        }

        if (!Hash::check($credentials['senha'], $usuario->senha)) {
            return null;
        }

        return $usuario;
    }
}
