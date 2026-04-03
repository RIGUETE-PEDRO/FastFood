<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;
use App\Services\AdminService;
use App\Http\Middleware\UsuarioAutenticado;
use Illuminate\Http\Request;
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
        $usuarioLogado =  $this->genericBase->hasLogado();
        $this->adminService->verificarAcessoPerfil();

        $perfilReturnUrl = $this->adminService->resolverReturnUrl($request);

        return view('Perfil', [
            'usuario' => $usuarioLogado,
            'tipoUsuario' => $this->mapearTipoUsuario($usuarioLogado->tipo_usuario_id ?? null),
            'perfilReturnUrl' => $perfilReturnUrl,
        ]);
    }

    //pegar nome do usuario para mostrar no administrativo
    public function nomeUsuario()
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
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
