<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    //função de registro de usuário
    public function register(array $data)
    {
        // Verifica se o email já está cadastrado
        if (Usuario::where('email', $data['email'])->exists()) {
            return redirect()->back()->with('erro', 'esse E-mail já encontra cadastrado.');
        }

         // Verifica o tamanho da senha
        if (strlen($data['senha']) < 6) {
            return redirect()->back()->with('erro', 'A senha deve ter pelo menos 6 caracteres.');
        }

        // Verifica se as senhas coincidem
        if ($data['senha'] !== $data['senha_confirmation']) {
            return redirect()->back()->with('erro', 'As senhas não coincidem.');
        }

        // Cria o novo usuário no banco de dados
        Usuario::create([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => bcrypt($data['senha']),
            'telefone' => $data['telefone'],
            'tipo_usuario_id' => 1,
        ]);

        // Retorna sucesso
        return redirect()->back()
            ->with('sucesso', 'Cadastro realizado com sucesso!');
    }


    

}
