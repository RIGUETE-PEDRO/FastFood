<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;
use App\Services\AdminService;
use App\Http\Middleware\UsuarioAutenticado;

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

    public function infoPerfil()
    {
        $user = session('usuario_logado');

        return view('Admin.perfil', [
            'usuario' => $user,
            'tipoUsuario' => $this->mapearTipoUsuario($user->tipo_usuario_id ?? null),
        ]);
    }


    public function nomeUsuario()
    {
        $user = session('usuario_logado');

        return view('Admin.Administrativo', [
            'usuario' => $user,
            'nomeUsuario' => $user->nome,
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
