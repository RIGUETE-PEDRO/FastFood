<?php

namespace App\Mensagens;


class ErroMensagens{
    public const NAO_ENCONTRAMOS_ENDERECO = 'Informe pelo menos bairro, rua e Cidade para cadastrar um novo endereço.';
    public const NAO_LOGADO_ENDERECO = 'Faça login para selecionar um endereço.';
    public const ENDEREÇO_NAO_ENCONTRADO = 'Endereço não encontrado';
    public const PRECISA_ESTA_LOGADO = 'Faça login para acessar essa funcionalidade.';
    public const Email_JA_CADASTRADO = 'Esse E-mail já encontra cadastrado.';
    public const SENHA_INVALIDA = 'Senha inválida.';
    public const USUARIO_NAO_ENCONTRADO = 'Usuário não encontrado.';

    public const MIN_CARACTERES_SENHA = 'A senha deve ter pelo menos 6 caracteres.';
    public const FAZER_LOGIN_PARA_ACESSAR = 'Faça login para acessar essa funcionalidade.';
    public const SENHAS_NAO_COINCIDEM = 'As senhas não coincidem';
    public const CREDENCIAIS_INVALIDAS = 'Credenciais inválidas ou usuário não tem permisão ativo.';
    public const EMAIL_NAO_CADASTRADO = 'E-mail não cadastrado.';
    public const ERRO_REGISTRAR_USUARIO = 'Ocorreu um erro ao registrar o usuário. Por favor, tente novamente.';
    public const ERRO_REGISTRAR_FUNCIONARIO = 'Ocorreu um erro ao registrar o funcionário. Por favor, tente novamente.';
    public const EMAIL_OBRIGATORIO = 'O campo de e-mail é obrigatório.';
    public const SENHA_OBRIGATORIA = 'O campo de senha é obrigatório.';
    public const EMAIL_NAO_CADASTRADDO = 'E-mail não cadastrado.';
    public const EMAIL_NAO_VALIDO = 'Digite um e-mail válido.';
    public const ERRO_PROCESSAR = 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.';
    public const ERRO_REDEFINIR_SENHA = 'Ocorreu um erro ao redefinir a senha. Por favor, tente novamente.';
    public const LINK_EXPIRADO = 'Este link de recuperação expirou. Solicite um novo.';
    public const TOKEN_EXPIRADO = 'Token inválido ou expirado.';
    public const ERRO_DELETAR_USUARIO = 'Ocorreu um erro ao deletar o usuário. Por favor, tente novamente.';
    public const QUANTIDADE_MINIMA = 'A quantidade mínima é 1, não é permitido 0.';
    public const NUMERO_MESA_INVALIDO = 'O número da mesa deve ser maior que zero.';
    public const SEM_ID_MESA = 'Nenhuma mesa selecionada para remoção.';

}
