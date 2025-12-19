<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Mail\RecuperarSenhaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoginController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'senha');

        $usuario = $this->authService->login($credentials);

        if (!$usuario) {
            return redirect()->back()->withErrors(['login' => 'Credenciais inválidas'])->withInput();
        }

        // Autenticação bem-sucedida, redirecionar para a página desejada
        return redirect()->route('home');
    }

    public function recuperarSenha(Request $request)
    {
        // Validação do email
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.exists' => 'Este e-mail não está cadastrado em nosso sistema.',
        ]);

        try {
            // Gerar token único
            $token = Str::random(60);

            // Deletar tokens antigos deste e-mail
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Salvar novo token no banco
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

            // Enviar e-mail com o link
            Mail::to($request->email)->send(new RecuperarSenhaMail($token, $request->email));

            return redirect()->back()
                ->with('sucesso', 'Um link de recuperação foi enviado para seu e-mail!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('erro', 'Erro ao processar solicitação: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function atualizarSenha(Request $request)
    {
        // Validação
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:usuarios,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.exists' => 'Este e-mail não está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
        ]);

        try {
            // Verificar se o token existe e é válido (menos de 60 minutos)
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->where('token', $request->token)
                ->first();

            if (!$resetRecord) {
                return redirect()->back()
                    ->with('erro', 'Token inválido ou expirado.')
                    ->withInput();
            }

            // Verificar se o token não está expirado (60 minutos)
            $createdAt = Carbon::parse($resetRecord->created_at);
            if ($createdAt->addMinutes(60)->isPast()) {
                return redirect()->back()
                    ->with('erro', 'Este link de recuperação expirou. Solicite um novo.')
                    ->withInput();
            }

            // Atualizar a senha do usuário
            DB::table('usuarios')
                ->where('email', $request->email)
                ->update([
                    'senha' => password_hash($request->password, PASSWORD_BCRYPT),
                    'updated_at' => Carbon::now(),
                ]);

            // Deletar o token usado
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return redirect()->route('login.form')
                ->with('sucesso', 'Senha redefinida com sucesso! Faça login com sua nova senha.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('erro', 'Erro ao redefinir senha: ' . $e->getMessage())
                ->withInput();
        }
    }
}
