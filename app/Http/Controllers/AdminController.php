<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;
use App\Services\AdminService;
use App\Http\Middleware\UsuarioAutenticado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Enum\TipoUsuario as EnumsTipoUsuario;

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
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();

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
            'usuario' => $usuarioLogado,
            'tipoUsuario' => $this->mapearTipoUsuario($usuarioLogado->tipo_usuario_id ?? null),
            'perfilReturnUrl' => $perfilReturnUrl,
        ]);
    }


    public function nomeUsuario()
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();

        // Pega somente o primeiro nome
        $primeiroNome = $usuarioLogado?->nome ? explode(' ', trim($usuarioLogado->nome))[0] : 'Usuário';


        return view('Admin.Administrativo', [
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $primeiroNome,
            'tipoUsuario' => $this->mapearTipoUsuario($usuarioLogado->tipo_usuario_id ?? null),
        ]);
    }

    public function AlterarDados()
    {
        return $this->adminService->InserirImagemPerfil();
    }


    private function mapearTipoUsuario(?int $tipoId): string
    {

        return EnumsTipoUsuario::tryFrom($tipoId)?->label() ?? 'Usuário';
    }
}
