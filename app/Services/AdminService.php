<?php

namespace App\Services;

use App\Mensagens\ErroMensagens;
use Illuminate\Http\Request;
use App\Mensagens\PassMensagens;
use App\Enum\StatusPedidos;
use App\Repository\AdminRepository;
use App\Roles\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class AdminService
{
    protected GenericBase $genericBase;
    protected AdminRepository $adminRepositoryimpl;
    protected KeyClockService $keyClockService;

    public function __construct(GenericBase $genericBase, AdminRepository $adminRepositoryimpl, KeyClockService $keyClockService)
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

    public function montarDashboardAdministrativo(?string $periodoSelecionado, ?string $referenciaSelecionada): array
    {
        $periodoDados = $this->resolverPeriodoDashboard($periodoSelecionado, $referenciaSelecionada);

        $inicioPeriodo = $periodoDados['inicioPeriodo'];
        $fimPeriodo = $periodoDados['fimPeriodo'];

        $totalVendas = $this->adminRepositoryimpl->totalVendasNoPeriodo($inicioPeriodo, $fimPeriodo);
        $totalPedidos = $this->adminRepositoryimpl->totalPedidosNoPeriodo($inicioPeriodo, $fimPeriodo);

        $produtoMaisVendido = $this->adminRepositoryimpl->produtoMaisVendidoNoPeriodo($inicioPeriodo, $fimPeriodo);
        $produtoMaisVendidoNome = $produtoMaisVendido?->produto_nome ?? 'Sem dados';
        $produtoMaisVendidoQtd = (int) ($produtoMaisVendido?->total_qtd ?? 0);

        $contagemStatus = $this->adminRepositoryimpl->contagemStatusNoPeriodo($inicioPeriodo, $fimPeriodo);

        $statusLabels = [];
        $statusValores = [];
        foreach (StatusPedidos::cases() as $status) {
            $statusLabels[] = $status->rotulo();
            $statusValores[] = (int) ($contagemStatus[$status->value] ?? 0);
        }

        $topProdutos = $this->adminRepositoryimpl->topProdutosNoPeriodo($inicioPeriodo, $fimPeriodo, 5);

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
            'periodoSelecionado' => $periodoDados['periodo'],
            'referenciaSelecionada' => $periodoDados['referenciaView'],
            'periodoTexto' => $periodoDados['periodoTexto'],
        ];
    }

    private function resolverPeriodoDashboard(?string $periodoSelecionado, ?string $referenciaSelecionada): array
    {
        $periodo = in_array($periodoSelecionado, ['dia', 'mes', 'ano'], true)
            ? $periodoSelecionado
            : 'mes';

        $agora = Carbon::now();
        $referenciaBruta = (string) ($referenciaSelecionada ?? '');

        if ($periodo === 'dia') {
            $referencia = $referenciaBruta !== '' ? Carbon::parse($referenciaBruta) : $agora->copy();
            $inicioPeriodo = $referencia->copy()->startOfDay();
            $fimPeriodo = $referencia->copy()->endOfDay();
            $referenciaView = $referencia->format('Y-m-d');
            $periodoTexto = 'Dia ' . $referencia->format('d/m/Y');
        } elseif ($periodo === 'ano') {
            $ano = (int) ($referenciaBruta !== '' ? $referenciaBruta : $agora->year);
            if ($ano < 2000 || $ano > 2100) {
                $ano = $agora->year;
            }

            $inicioPeriodo = Carbon::create($ano, 1, 1)->startOfDay();
            $fimPeriodo = Carbon::create($ano, 12, 31)->endOfDay();
            $referenciaView = (string) $ano;
            $periodoTexto = 'Ano ' . $ano;
        } else {
            if (preg_match('/^\d{4}-\d{2}$/', $referenciaBruta) === 1) {
                $referencia = Carbon::createFromFormat('Y-m', $referenciaBruta);
            } else {
                $referencia = $agora->copy();
            }

            $inicioPeriodo = $referencia->copy()->startOfMonth();
            $fimPeriodo = $referencia->copy()->endOfMonth();
            $referenciaView = $referencia->format('Y-m');
            $periodoTexto = 'Mês ' . ucfirst($referencia->translatedFormat('F/Y'));
        }

        return [
            'periodo' => $periodo,
            'inicioPeriodo' => $inicioPeriodo,
            'fimPeriodo' => $fimPeriodo,
            'referenciaView' => $referenciaView,
            'periodoTexto' => $periodoTexto,
        ];
    }
}
