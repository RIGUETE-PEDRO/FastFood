<?php

namespace App\Services;

use App\Mensagens\ErroMensagens;
use Illuminate\Http\Request;
use App\Mensagens\PassMensagens;
use App\Repositoryimpl\AdminRepositoryimpl;
use App\Roles\Role;
use Illuminate\Support\Facades\URL;

class AdminService
{
    protected GenericBase $genericBase;
    protected AdminRepositoryimpl $adminRepositoryimpl;
    protected KeyClockService $keyClockService;

    public function __construct(GenericBase $genericBase, AdminRepositoryimpl $adminRepositoryimpl, KeyClockService $keyClockService)
    {
        $this->genericBase = $genericBase;
        $this->adminRepositoryimpl = $adminRepositoryimpl;
        $this->keyClockService = $keyClockService;
    }

    public function inserirImagemPerfil(array $data, $file = null): array
    {
        $usuario = $this->genericBase->findById($data['id']);

        if (!$usuario) {
            return ['status' => false, 'mensagem' => 'Usuário não encontrado.'];
        }

        $usuario->nome = $data['nome'];
        $usuario->email = $data['email'];
        $usuario->telefone = $data['telefone'];

        if ($file) {
            if (
                $usuario->url_imagem_perfil &&
                file_exists(public_path('img/perfil/' . $usuario->url_imagem_perfil))
            ) {
                unlink(public_path('img/perfil/' . $usuario->url_imagem_perfil));
            }

            $fileName = 'perfil_' . $usuario->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('img/perfil'), $fileName);

            $usuario->url_imagem_perfil = $fileName;
        }

        $usuario->save();

        return [
            'status' => true,
            'mensagem' => "Dados " . PassMensagens::ATUALIZADO_SUCESSO,
            'usuario' => $usuario
        ];
    }

    public function resolverReturnUrl(Request $request)
    {
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

        return session('perfil_return_url', $fallbackUrl);
    }

    public function buscarFuncionarios($searchTerm)
    {
        $funcionarios = $this->adminRepositoryimpl->buscarFuncionarios($searchTerm);
        return $funcionarios;
    }


    public function verificarAcessoPerfil()
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $hasRole = $this->keyClockService->hasRole($usuarioLogado, Role::ADMIN);
        if (!$hasRole) {
            abort(403, ErroMensagens::ACESSO_NEGADO);
        }
    }

    public function listarFuncionarios()
    {
        return $this->genericBase->findFuncionarios();
    }
}
