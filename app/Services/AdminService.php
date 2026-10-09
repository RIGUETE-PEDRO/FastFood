<?php

namespace App\Services;

use App\Enum\StatusPedidos;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Repository\AdminRepository;
use App\Roles\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AdminService
{
    protected GenericBase $genericBase;
    protected AdminRepository $adminRepository;
    protected SecureKeyService $SecureKeyService;

    public function __construct(
        GenericBase $genericBase,
        AdminRepository $adminRepository,
        SecureKeyService $SecureKeyService
    ) {
        $this->genericBase = $genericBase;
        $this->adminRepository = $adminRepository;
        $this->SecureKeyService = $SecureKeyService;
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
            'mensagem' => 'Dados ' . PassMensagens::ATUALIZADO_SUCESSO,
            'usuario' => $usuario
        ];
    }

    public function resolverReturnUrl(Request $request)
    {
        $fallbackUrl = route('home');

        $returnUrl = $this->normalizarUrlRetornoPerfil($request->query('return'), $request);
        if ($returnUrl !== null) {
            session(['perfil_return_url' => $returnUrl]);
            return $returnUrl;
        }

        $previousUrl = $this->normalizarUrlRetornoPerfil(URL::previous(), $request);
        if ($previousUrl !== null) {
            session(['perfil_return_url' => $previousUrl]);
            return $previousUrl;
        }

        return $this->normalizarUrlRetornoPerfil(session('perfil_return_url'), $request) ?? $fallbackUrl;
    }

    private function normalizarUrlRetornoPerfil($url, Request $request): ?string
    {
        if (!is_string($url) || trim($url) === '') {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && strcasecmp($parts['host'], $request->getHost()) !== 0) {
            return null;
        }

        if (isset($parts['scheme']) && !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        $pathNormalizado = '/' . trim(strtolower(urldecode($path)), '/');
        $rotasBloqueadas = [
            '/' . trim(parse_url(route('perfil'), PHP_URL_PATH) ?: '/perfil', '/'),
            '/' . trim(parse_url(route('AcessoNegado'), PHP_URL_PATH) ?: '/AcessoNegado', '/'),
            '/' . trim(parse_url(route('Alterar_Dados'), PHP_URL_PATH) ?: '/Alterar_Dados', '/'),
        ];

        if (in_array($pathNormalizado, array_map('strtolower', $rotasBloqueadas), true)) {
            return null;
        }

        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    public function camposDadosEmpresa(): array
    {
        return $this->adminRepository
            ->listarDadosEmpresa()
            ->map(function ($dado) {
                return [
                    'informacao' => $dado->Informacao,
                    'label' => $this->formatarLabelDadoEmpresa($dado->Informacao),
                    'type' => $this->resolverTipoCampoDadoEmpresa($dado->Informacao),
                    'value' => $dado->Valor ?? '',
                ];
            })
            ->all();
    }

    public function atualizarDadosEmpresa(array $dados): void
    {
        $dadosExistentes = $this->adminRepository
            ->listarDadosEmpresa()
            ->keyBy('Informacao');

        foreach ($dados as $informacao => $valor) {
            if (!$dadosExistentes->has($informacao)) {
                continue;
            }

            $valor = is_string($valor) ? trim($valor) : null;
            $valorAntigo = $dadosExistentes->get($informacao)?->Valor;

            if ($valorAntigo === $valor) {
                continue;
            }

            $this->adminRepository->atualizarDadoEmpresa($informacao, $valor);
        }
    }

    private function formatarLabelDadoEmpresa(string $informacao): string
    {
        if ($informacao === 'Msg_comanda') {
            return 'Msg da comanda';
        }

        return str_replace('_', ' ', $informacao);
    }

    private function resolverTipoCampoDadoEmpresa(string $informacao): string
    {
        return match ($informacao) {
            'Email' => 'email',
            'Telefone' => 'tel',
            'Msg_comanda' => 'textarea',
            default => 'text',
        };
    }

    public function buscarFuncionarios($searchTerm)
    {
        return $this->adminRepository->buscarFuncionarios($searchTerm);
    }

    public function verificarAcessoPerfil()
    {
        $usuarioLogado = $this->genericBase->hasLogado();
        $hasRole = $this->SecureKeyService->hasRole($usuarioLogado, Roles::ADMIN);

        if (!$hasRole) {
            abort(403, ErroMensagens::ACESSO_NEGADO);
        }
    }

    public function listarFuncionarios()
    {  
        
        return $this->genericBase->findFuncionarios();
    }

    public function montarDashboardAdministrativo(?string $dataInicioSelecionada, ?string $dataFimSelecionada): array
    {
        $periodoDados = $this->resolverPeriodoDashboard($dataInicioSelecionada, $dataFimSelecionada);

        $inicioPeriodo = $periodoDados['inicioPeriodo'];
        $fimPeriodo = $periodoDados['fimPeriodo'];

        $totalVendas = $this->adminRepository->totalVendasNoPeriodo($inicioPeriodo, $fimPeriodo);
        $totalPedidos = $this->adminRepository->totalPedidosNoPeriodo($inicioPeriodo, $fimPeriodo);

        $produtoMaisVendido = $this->adminRepository->produtoMaisVendidoNoPeriodo($inicioPeriodo, $fimPeriodo);
        $produtoMaisVendidoNome = $produtoMaisVendido?->produto_nome ?? 'Sem dados';
        $produtoMaisVendidoQtd = (int) ($produtoMaisVendido?->total_qtd ?? 0);

        $contagemStatus = $this->adminRepository->contagemStatusNoPeriodo($inicioPeriodo, $fimPeriodo);

        $statusLabels = [];
        $statusValores = [];
        foreach (StatusPedidos::cases() as $status) {
            $statusLabels[] = $status->rotulo();
            $statusValores[] = (int) ($contagemStatus[$status->value] ?? 0);
        }

        $topProdutos = $this->adminRepository->topProdutosNoPeriodo($inicioPeriodo, $fimPeriodo, 5);
        $pedidosPorMotoboy = $this->adminRepository->pedidosPorMotoboyNoPeriodo($inicioPeriodo, $fimPeriodo);

        $topProdutosLabels = $topProdutos
            ->map(fn($item) => $item->produto_nome ?? 'Produto removido')
            ->values()
            ->all();

        $topProdutosValores = $topProdutos
            ->map(fn($item) => (int) ($item->total_qtd ?? 0))
            ->values()
            ->all();

        if (empty($topProdutosLabels)) {
            $topProdutosLabels = ['Sem dados'];
            $topProdutosValores = [0];
        }

        return [
            'totalVendas' => $totalVendas,
            'totalPedidos' => $totalPedidos,
            'produtoMaisVendidoNome' => $produtoMaisVendidoNome,
            'produtoMaisVendidoQtd' => $produtoMaisVendidoQtd,
            'statusLabels' => $statusLabels,
            'statusValores' => $statusValores,
            'topProdutosLabels' => $topProdutosLabels,
            'topProdutosValores' => $topProdutosValores,
            'pedidosPorMotoboy' => $pedidosPorMotoboy,
            'dataInicioSelecionada' => $periodoDados['dataInicioView'],
            'dataFimSelecionada' => $periodoDados['dataFimView'],
            'periodoTexto' => $periodoDados['periodoTexto'],
        ];
    }

    private function resolverPeriodoDashboard(?string $dataInicioSelecionada, ?string $dataFimSelecionada): array
    {
        $temFiltroManual = filled($dataInicioSelecionada) || filled($dataFimSelecionada);
        $basePeriodo = $temFiltroManual
            ? Carbon::now()
            : ($this->adminRepository->dataUltimoPedido() ?? Carbon::now());

        $inicio = $this->normalizarDataDashboard($dataInicioSelecionada)
            ?? $basePeriodo->copy()->startOfMonth();
        $fim = $this->normalizarDataDashboard($dataFimSelecionada)
            ?? $basePeriodo->copy()->endOfMonth();

        if ($inicio->greaterThan($fim)) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        $inicioPeriodo = $inicio->copy()->startOfDay();
        $fimPeriodo = $fim->copy()->endOfDay();
        $dataInicioView = $inicioPeriodo->format('Y-m-d');
        $dataFimView = $fimPeriodo->format('Y-m-d');
        $periodoTexto = $dataInicioView === $dataFimView
            ? 'Dia ' . $inicioPeriodo->format('d/m/Y')
            : $inicioPeriodo->format('d/m/Y') . ' ate ' . $fimPeriodo->format('d/m/Y');

        return [
            'inicioPeriodo' => $inicioPeriodo,
            'fimPeriodo' => $fimPeriodo,
            'dataInicioView' => $dataInicioView,
            'dataFimView' => $dataFimView,
            'periodoTexto' => $periodoTexto,
        ];
    }

    private function normalizarDataDashboard(?string $data): ?Carbon
    {
        $data = trim((string) $data);
        if ($data === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) !== 1) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $data);
        } catch (\Throwable) {
            return null;
        }
    }
}
