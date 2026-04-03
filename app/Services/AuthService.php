<?php

namespace App\Services;

use App\Repositoryimpl\AuthRepositoryimpl;
use Illuminate\Support\Facades\Hash;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;

class AuthService
{
    protected GenericBase $genericBase;
    protected AuthRepositoryimpl $authRepository;

    public function __construct(GenericBase $genericBase, AuthRepositoryimpl $authRepository)
    {
        $this->genericBase = $genericBase;
        $this->authRepository = $authRepository;
    }

    //função de registro de usuário
    public function register(array $data)
    {
        // Verifica se o email já está cadastrado
        if ($this->genericBase->pegarUsuarioEmail($data) !== null) {
            return redirect()->back()->with('erro', ErroMensagens::EMAIL_JA_CADASTRADO);
        }

         // Verifica o tamanho da senha
        if (strlen($data['senha']) < 6) {
            return redirect()->back()->with('erro', ErroMensagens::MIN_CARACTERES_SENHA);
        }

        // Verifica se as senhas coincidem
        if ($data['senha'] !== $data['senha_confirmation']) {
            return redirect()->back()->with('erro', ErroMensagens::SENHAS_NAO_COINCIDEM);
        }

        // Cria o novo usuário no banco de dados
        $usuarioCriado = $this->authRepository->criarUsuario([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => bcrypt($data['senha']),
            'telefone' => $data['telefone'],
            'tipo_usuario_id' => $data['tipo_usuario_id'],
            'url_imagem_perfil' => 'personPadrao.svg'
        ]);

        if ($data['tipo_usuario_id'] !== 1 && $data['tipo_usuario_id'] !== null) {


            $this->authRepository->criarFuncionario([
                'usuario_id' => $usuarioCriado->id,
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
        $usuarioExistente = $this->genericBase->pegarUsuarioEmail($usuario);

        if ($usuarioExistente && Hash::check($usuario['senha'], $usuarioExistente->senha)) {
            // Verifica se o usuário é um funcionário
            $funcionario = $this->genericBase->existeFuncionario($usuarioExistente);
            if ($funcionario) {
                // Verifica se o funcionário está ativo
                if ($funcionario->has_ativo) {
                    return $usuarioExistente;
                } else {
                    return redirect('AcessoNegado')->with('erro', ErroMensagens::CREDENCIAIS_INVALIDAS);
                }
            } else {
                return redirect('AcessoNegado')->with('erro', ErroMensagens::CREDENCIAIS_INVALIDAS);
            }
        }

        return redirect('AcessoNegado')->with('erro', ErroMensagens::CREDENCIAIS_INVALIDAS);
    }




}
