<?php

namespace App\Services;

use App\Mail\RecuperarSenhaMail;
use Illuminate\Support\Facades\Hash;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Repositoryimpl\LoginRepositoryimpl;
use App\Roles\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginService
{
    protected GenericBase $genericBase;
    protected keyclockService $keyclockService;
    protected LoginRepositoryimpl $loginRepository;
    public function __construct(GenericBase $genericBase, keyclockService $keyclockService, LoginRepositoryimpl $loginRepository)
    {
        $this->genericBase = $genericBase;
        $this->keyclockService = $keyclockService;
        $this->loginRepository = $loginRepository;
    }
    //função de autenticação de usuário
    public function autenticar($credenciais)
    {
        $autenticou = false;
        // Busca o usuário pelo email
        $usuario = $this->loginRepository->buscarUsuarioPorEmail($credenciais['email']);

        if (!$usuario || !Hash::check($credenciais['senha'], $usuario->senha)) {

            redirect()->back()->with('erro', ErroMensagens::CREDENCIAIS_INVALIDAS);
            return $autenticou = false;
        }

        if (!$this->loginRepository->existeUsuarioPorEmail($credenciais['email'])) {
            redirect()->back()->with('erro', ErroMensagens::EMAIL_NAO_CADASTRADO);
            return $autenticou = false;
        }

        $autenticou = true;
        return $autenticou;
    }


    public function validarLogin($request)
    {
        $credenciais = $request->only('email', 'senha');

        $usuario = $this->genericBase->findAll()->where('email', $credenciais['email'])->first();

        $autenticador = $this->autenticar($credenciais);

        if ($autenticador === false) {
            return redirect()->back()->withErrors(['login' => ErroMensagens::CREDENCIAIS_INVALIDAS])->withInput();
        }

        // Salvar usuário na sessão
        session(['usuario_logado' => $usuario]);


        Auth::login($usuario);



        if (!$autenticador) {
            return redirect()->route('login');
        }
        //altenticação de usuário e redirecionamento para a página correta com base no tipo de usuário do keyclock
        if ($this->keyclockService->hasRole($usuario, Role::KEYCLOCK)) {
            return redirect()->route('keyclock.index');
        }

        if ($this->keyclockService->hasRole($usuario, Role::ADMIN)) {
            return redirect()->route('Administrativo');
        }

        if ($autenticador == true) {
            return redirect()->route('home');
        }
    }

    public function recuperarSenha($request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ], [
            'email.required' => ErroMensagens::EMAIL_OBRIGATORIO,
            'email.email' => ErroMensagens::EMAIL_NAO_VALIDO,
            'email.exists' => ErroMensagens::EMAIL_NAO_CADASTRADDO,
        ]);

        try {
            // Gerar token único
            $token = Str::random(60);

            // Deletar tokens antigos deste e-mail
            $this->loginRepository->deletarTokensRecuperacao($request->email);

            // Salvar novo token no banco
            $this->loginRepository->inserirTokenRecuperacao($request->email, $token);

            // Enviar e-mail com o link
            Mail::to($request->email)->send(new RecuperarSenhaMail($token, $request->email));

            return redirect()->back()
                ->with('sucesso', PassMensagens::ENVIAR_LINK_RECUPERACAO);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('erro', ErroMensagens::ERRO_PROCESSAR . $e->getMessage())
                ->withInput();
        }
    }

    public function atualizarSenha($request){
        // Validação
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:usuarios,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => ErroMensagens::EMAIL_OBRIGATORIO,
            'email.email' => ErroMensagens::EMAIL_NAO_VALIDO,
            'email.exists' => ErroMensagens::EMAIL_NAO_CADASTRADDO,
            'password.required' => ErroMensagens::SENHA_OBRIGATORIA,
            'password.min' => ErroMensagens::MIN_CARACTERES_SENHA,
            'password.confirmed' => ErroMensagens::SENHAS_NAO_COINCIDEM,
        ]);

        try {
            // Verificar se o token existe e é válido (menos de 60 minutos)
            $resetRecord = $this->loginRepository->buscarTokenRecuperacao($request->email, $request->token);

            if (!$resetRecord) {
                return redirect()->back()
                    ->with('erro', ErroMensagens::TOKEN_EXPIRADO)
                    ->withInput();
            }

            // Verificar se o token não está expirado (60 minutos)
            $createdAt = Carbon::parse($resetRecord->created_at);
            if ($createdAt->addMinutes(60)->isPast()) {
                return redirect()->back()
                    ->with('erro', ErroMensagens::LINK_EXPIRADO)
                    ->withInput();
            }

            // Atualizar a senha do usuário
            $this->loginRepository->atualizarSenhaUsuario(
                $request->email,
                password_hash($request->password, PASSWORD_BCRYPT)
            );

            // Deletar o token usado
            $this->loginRepository->deletarTokensRecuperacao($request->email);

            return redirect()->route('login.form')
                ->with('sucesso', PassMensagens::SENHA_REDEFINIDA_SUCESSO);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('erro', ErroMensagens::ERRO_REDEFINIR_SENHA . $e->getMessage())
                ->withInput();
        }
    }
}
