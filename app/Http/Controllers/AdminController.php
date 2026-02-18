<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;
use App\Services\AdminService;
use App\Http\Middleware\UsuarioAutenticado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AdminController extends Controller
{
    protected GenericBase $genericBase;
    protected AdminService $adminService;
    protected UsuarioAutenticado $authMiddleware;

    public function __construct(GenericBase $genericBase, AdminService $adminService, UsuarioAutenticado $authMiddleware)
    {
        $this->genericBase = $genericBase;
        $this->adminService = $adminService;
        $this->authMiddleware = $authMiddleware;
    }

    public function infoPerfil(Request $request)
    {
        $user = session('usuario_logado');

        $previousUrl = URL::previous();
        $currentUrl = $request->fullUrl();

        $fallbackUrl = route('home');
        $rotasBloqueadas = [
            route('AcessoNegado'),
            route('Alterar_Dados'),
        ];

        if (in_array($previousUrl, $rotasBloqueadas, true) || $previousUrl === $currentUrl) {
            $previousUrl = session('perfil_return_url', $fallbackUrl);
        }

        if (!empty($previousUrl) && $previousUrl !== $currentUrl) {
            session(['perfil_return_url' => $previousUrl]);
        }

        $perfilReturnUrl = session('perfil_return_url', $fallbackUrl);

        return view('Perfil', [
            'usuario' => $user,
            'tipoUsuario' => $this->mapearTipoUsuario($user->tipo_usuario_id ?? null),
            'perfilReturnUrl' => $perfilReturnUrl,
        ]);
    }


    public function nomeUsuario()
    {
        $user = session('usuario_logado');

        // Pega somente o primeiro nome
        $primeiroNome = explode(' ', trim($user->nome))[0];


        return view('Admin.Administrativo', [
            'usuario' => $user,
            'nomeUsuario' => $primeiroNome,
            'tipoUsuario' => $this->mapearTipoUsuario($user->tipo_usuario_id ?? null),
        ]);
    }

    public function AlterarDados()
    {
        return $this->adminService->InserirImagemPerfil();
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
