<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\LoginService;
use App\Mail\RecuperarSenhaMail;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Services\GenericBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoginController extends Controller
{
    protected AuthService $authService;
    protected LoginService $loginService;
    protected GenericBase $genericBase;

    //construtor para injeção de dependências
    public function __construct(
        AuthService $authService,
        LoginService $loginService,
        GenericBase $genericBase
    ) {
        $this->authService = $authService;
        $this->loginService = $loginService;
        $this->genericBase = $genericBase;
    }


    public function logout()
    {
        return $this->genericBase->logout();
    }


    public function login(Request $request)
    {
        return $this->loginService->validarLogin($request);

    }

    public function recuperarSenha(Request $request)
    {
        return $this->loginService->recuperarSenha($request);

    }

    public function atualizarSenha(Request $request)
    {

        return $this->loginService->atualizarSenha($request);

    }
}
