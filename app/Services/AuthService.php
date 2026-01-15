<?php

namespace App\Services;

use App\Models\Funcionario;
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
            'tipo_usuario_id' => $data['tipo_usuario_id'],
            'url_imagem_perfil' => 'personPadrao.svg'
        ]);

        if ($data['tipo_usuario_id'] !== 1 && $data['tipo_usuario_id'] !== null) {

            Funcionario::create([
                'usuario_id' => Usuario::where('email', $data['email'])->first()->id,
                'has_ativo' => $data['has_ativo'] ?? true,
                'salario' => $data['salario'] ?? 0.00,
            ]);
        }


        // Retorna sucesso
        return redirect()->back()
            ->with('sucesso', 'Cadastro realizado com sucesso!');
    }

    public function autenticarAdm($usuario)
    {
        $usuarioExistente = Usuario::where('email', $usuario['email'])->first();

        if ($usuarioExistente && Hash::check($usuario['senha'], $usuarioExistente->senha)) {
            // Verifica se o usuário é um funcionário
            $funcionario = Funcionario::where('usuario_id', $usuarioExistente->id)->first();
            if ($funcionario) {
                // Verifica se o funcionário está ativo
                if ($funcionario->has_ativo) {
                    return $usuarioExistente;
                } else {
                    return redirect('AcessoNegado')->with('erro', 'Credenciais inválidas ou usuário não tem permisão ativo.');
                }
            } else {
                return redirect('AcessoNegado')->with('erro', 'Credenciais inválidas ou usuário não tem permisão ativo.');
            }
        }

        return redirect('AcessoNegado')->with('erro', 'Credenciais inválidas ou usuário não tem permisão ativo.');
       
    }




}
