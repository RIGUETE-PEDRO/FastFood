<?php

namespace App\Services;

use App\Models\Funcionario;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use App\Mensagens\ErroMessages;
use App\Mensagens\PassMensagens;

class AuthService
{
    //função de registro de usuário
    public function register(array $data)
    {
        // Verifica se o email já está cadastrado
        if (Usuario::where('email', $data['email'])->exists()) {
            return redirect()->back()->with('erro', ErroMessages::Email_JA_CADASTRADO);
        }

         // Verifica o tamanho da senha
        if (strlen($data['senha']) < 6) {
            return redirect()->back()->with('erro', ErroMessages::MIN_CARACTERES_SENHA);
        }

        // Verifica se as senhas coincidem
        if ($data['senha'] !== $data['senha_confirmation']) {
            return redirect()->back()->with('erro', ErroMessages::SENHAS_NAO_COINCIDEM);
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
            ->with('sucesso', PassMensagens::CADASTRO_REALIZADO);
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
                    return redirect('AcessoNegado')->with('erro', ErroMessages::CREDENCIAIS_INVALIDAS);
                }
            } else {
                return redirect('AcessoNegado')->with('erro', ErroMessages::CREDENCIAIS_INVALIDAS);
            }
        }

        return redirect('AcessoNegado')->with('erro', ErroMessages::CREDENCIAIS_INVALIDAS);
    }




}
