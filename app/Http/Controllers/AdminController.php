<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;
use App\Services\AdminService;
use App\Http\Middleware\UsuarioAutenticado;
use App\Mensagens\PassMensagens;
use App\Models\ConfiguracaoImpressao;
use Illuminate\Http\Request;
use App\Enum\TipoUsuario as EnumsTipoUsuario;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    private const DEFAULT_USER_NAME = 'Usuário';

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
        $usuarioLogado = $this->genericBase->hasLogado() ?? Auth::user();

        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', 'Faça login para acessar seu perfil.');
        }

        $perfilReturnUrl = $this->adminService->resolverReturnUrl($request);

        return view('Perfil', [
            'usuario' => $usuarioLogado,
            'tipoUsuario' => $this->mapearTipoUsuario($usuarioLogado->tipo_usuario_id ?? null),
            'perfilReturnUrl' => $perfilReturnUrl,
        ]);
    }

    //pegar nome do usuario para mostrar no administrativo
    public function nomeUsuario(Request $request)
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $primeiroNome = $usuarioLogado?->nome ? explode(' ', trim($usuarioLogado->nome))[0] : self::DEFAULT_USER_NAME;

        $dashboard = $this->adminService->montarDashboardAdministrativo(
            $request->query('data_inicio'),
            $request->query('data_fim')
        );

        return view('Admin.Administrativo', [
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $primeiroNome,
            'tipoUsuario' => $this->mapearTipoUsuario($usuarioLogado->tipo_usuario_id ?? null),
            ...$dashboard,
        ]);
    }

    public function bemVindo(Request $request)
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $primeiroNome = $usuarioLogado?->nome ? explode(' ', trim($usuarioLogado->nome))[0] : self::DEFAULT_USER_NAME;

        return view('Admin.BemVindo', [
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $primeiroNome,
            'tipoUsuario' => $this->mapearTipoUsuario($usuarioLogado->tipo_usuario_id ?? null),
        ]);
    }

    public function configuracoes()
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $primeiroNome = $usuarioLogado?->nome ? explode(' ', trim($usuarioLogado->nome))[0] : self::DEFAULT_USER_NAME;

        return view('Admin.Configuracoes', [
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $primeiroNome,
            'tipoUsuario' => $this->mapearTipoUsuario($usuarioLogado->tipo_usuario_id ?? null),
            'camposEmpresa' => $this->adminService->camposDadosEmpresa(),
        ]);
    }

    public function atualizarConfiguracoes(Request $request)
    {
        $dados = $request->validate([
            'dados_empresa' => ['required', 'array'],
            'dados_empresa.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->adminService->atualizarDadosEmpresa($dados['dados_empresa']);

        return redirect()
            ->route('admin.configuracoes')
            ->with('sucesso', 'Dados da empresa ' . PassMensagens::ATUALIZADO_SUCESSO);
    }

    public function configuracoesImpressao(Request $request)
    {
        return view('Admin.Impressao', [
            'configuracoesImpressao' => ConfiguracaoImpressao::atuais(),
            'impressaoReturnUrl' => $this->resolverUrlRetornoImpressao($request),
        ]);
    }

    public function atualizarConfiguracoesImpressao(Request $request)
    {
        $dados = $request->validate([
            'paperWidth' => ['required', 'in:58,80'],
            'fontSize' => ['required', 'in:10,12,14'],
            'receiptFont' => ['required', 'in:monospace,arial,georgia'],
            'copies' => ['required', 'integer', 'between:1,5'],
            'padding' => ['required', 'in:compact,normal'],
            // O formulário pode enviar booleanos como strings ("true"/"false")
            // ou como 1/0, dependendo do cliente.
            'autoPrintOnAccept' => ['sometimes', 'in:true,false,1,0'],
        ]);

        $dados['autoPrintOnAccept'] = filter_var(
            $request->input('autoPrintOnAccept', false),
            FILTER_VALIDATE_BOOLEAN
        );

        ConfiguracaoImpressao::salvar($dados);

        return redirect()
            ->route('admin.impressao', ['return' => $this->resolverUrlRetornoImpressao($request)])
            ->with('sucesso', 'Preferências de impressão salvas.');
    }

    private function resolverUrlRetornoImpressao(Request $request): string
    {
        $returnUrl = $request->input('return');
        if (!is_string($returnUrl) || trim($returnUrl) === '') {
            return route('admin.configuracoes');
        }

        $parts = parse_url($returnUrl);
        if ($parts === false || isset($parts['host']) || isset($parts['scheme'])) {
            return route('admin.configuracoes');
        }

        $path = $parts['path'] ?? '';
        if (!str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return route('admin.configuracoes');
        }

        $impressaoPath = parse_url(route('admin.impressao'), PHP_URL_PATH);
        if (rtrim($path, '/') === rtrim($impressaoPath ?: '/admin/configuracoes/impressao', '/')) {
            return route('admin.configuracoes');
        }

        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    public function alterarDados(Request $request)
    {
        $user = session('usuario_logado') ?? Auth::user();

        if (!$user) {
            return redirect()->route('login.form')->with('erro', 'Faça login para editar seu perfil.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'url_imagem_perfil' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $dados = $request->only('nome', 'email', 'telefone');
        $dados['id'] = $user->id;

        $resultado = $this->adminService->inserirImagemPerfil(
            $dados,
            $request->file('url_imagem_perfil')
        );

        if (!$resultado['status']) {
            return redirect()->route('perfil')->with('erro', $resultado['mensagem']);
        }

        session(['usuario_logado' => $resultado['usuario']]);

        return redirect()->route('perfil')->with('sucesso', $resultado['mensagem']);
    }


    private function mapearTipoUsuario(?int $tipoId): string
    {
        return EnumsTipoUsuario::tryFrom($tipoId)?->label() ?? 'Usuário';
    }
}
