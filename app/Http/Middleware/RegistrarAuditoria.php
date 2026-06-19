<?php

namespace App\Http\Middleware;

use App\Services\AuditoriaRegistroService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegistrarAuditoria
{
    public function __construct(private AuditoriaRegistroService $auditoriaRegistroService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $inicio = microtime(true);
        $response = $next($request);

        if (Auth::check()) {
            $this->registrarRequisicao($request, $response, $inicio);
        }

        return $response;
    }

    private function registrarRequisicao(Request $request, $response, float $inicio): void
    {
        $metodo = $request->method();
        $rota = $request->route()?->getName() ?? $request->path();

        if ($this->deveIgnorarRota($request, $rota, $metodo)) {
            return;
        }

        $this->registrarOperacao($request, $rota, $metodo, $response, $inicio);
    }

    private function deveIgnorarRota(Request $request, string $rota, string $metodo): bool
    {
        if ($metodo === 'HEAD') {
            return true;
        }

        if ($request->ajax() || $request->expectsJson() || $request->boolean('polling')) {
            return true;
        }

        $path = $request->path();
        $ignorados = [
            'up',
            '_debugbar',
            'livewire',
            'sanctum/csrf-cookie',
            'favicon.ico',
            'SecureKey.auditoria',
            'admin.configuracoes.atualizar',
            'login',
            'logout',
            'cypress-login-admin',
        ];

        foreach ($ignorados as $item) {
            if (str_contains($path, $item) || str_contains($rota, $item)) {
                return true;
            }
        }

        return false;
    }

    private function registrarOperacao(
        Request $request,
        string $rota,
        string $metodo,
        $response,
        float $inicio
    ): void {
        $acao = $this->mapearAcao($metodo, $rota);
        $recurso = $this->extrairRecurso($rota);
        $route = $request->route();
        $statusHttp = method_exists($response, 'getStatusCode')
            ? $response->getStatusCode()
            : null;

        $detalhes = [
            'requisicao' => [
                'metodo' => $metodo,
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'rota' => $rota,
                'controlador' => $route?->getActionName(),
                'parametros_rota' => $this->sanitizarDados($route?->parameters() ?? []),
                'query' => $this->sanitizarDados($request->query()),
                'corpo' => $this->sanitizarDados($request->except(array_keys($request->query()))),
                'ip' => $request->ip(),
                'ips_encaminhados' => $request->ips(),
                'ajax' => $request->ajax(),
                'conteudo_tipo' => $request->header('content-type'),
                'idioma' => $request->getPreferredLanguage(),
                'cabecalhos' => $this->cabecalhosSeguros($request),
            ],
            'resposta' => [
                'status' => $statusHttp,
                'sucesso' => $statusHttp !== null && $statusHttp >= 200 && $statusHttp < 400,
                'conteudo_tipo' => isset($response->headers)
                    ? $response->headers->get('content-type')
                    : null,
                'redirecionamento' => method_exists($response, 'isRedirection') && $response->isRedirection()
                    ? $response->headers->get('location')
                    : null,
            ],
            'execucao' => [
                'duracao_ms' => round((microtime(true) - $inicio) * 1000, 2),
                'memoria_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'registrado_em' => now()->toIso8601String(),
            ],
        ];

        try {
            $this->auditoriaRegistroService->registrar($acao, $recurso, $detalhes);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar auditoria: ' . $e->getMessage());
        }
    }

    private function mapearAcao(string $metodo, string $rota): string
    {
        $rotaLower = strtolower($rota);

        if (str_contains($rotaLower, 'deletar') || str_contains($rotaLower, 'destroy') || str_contains($rotaLower, 'remover')) {
            return 'excluir';
        }

        if (str_contains($rotaLower, 'atualizar') || str_contains($rotaLower, 'update') || str_contains($rotaLower, 'alterar')) {
            return 'atualizar';
        }

        if (str_contains($rotaLower, 'login')) {
            return 'login';
        }

        if (str_contains($rotaLower, 'logout')) {
            return 'logout';
        }

        return match ($metodo) {
            'POST' => 'criar',
            'PUT', 'PATCH' => 'atualizar',
            'DELETE' => 'excluir',
            'GET' => 'visualizar',
            default => 'acessar',
        };
    }

    private function extrairRecurso(string $rota): string
    {
        $partes = explode('.', $rota);
        $recurso = $partes[0] ?? 'desconhecido';

        return ucfirst(rtrim($recurso, 's'));
    }

    private function sanitizarDados(array $dados): array
    {
        $chavesSensiveis = [
            'senha',
            'password',
            'token',
            'secret',
            'api_key',
            'numero_cartao',
            'cvv',
            'pin',
            'senha_antiga',
            '_token',
            'csrf',
            'authorization',
            'cookie',
        ];

        $dadosSanitizados = [];

        foreach ($dados as $chave => $valor) {
            $chaveLower = strtolower((string) $chave);
            $ehSensivel = false;

            foreach ($chavesSensiveis as $sensivel) {
                if (str_contains($chaveLower, $sensivel)) {
                    $ehSensivel = true;
                    break;
                }
            }

            if ($ehSensivel) {
                $dadosSanitizados[$chave] = '[REDACTED]';
            } elseif ($valor instanceof UploadedFile) {
                $dadosSanitizados[$chave] = [
                    'arquivo' => $valor->getClientOriginalName(),
                    'tipo' => $valor->getClientMimeType(),
                    'tamanho_bytes' => $valor->getSize(),
                ];
            } elseif (is_array($valor)) {
                $dadosSanitizados[$chave] = $this->sanitizarDados($valor);
            } elseif (is_object($valor)) {
                $dadosSanitizados[$chave] = method_exists($valor, 'getKey')
                    ? ['tipo' => $valor::class, 'id' => $valor->getKey()]
                    : ['tipo' => $valor::class];
            } else {
                $dadosSanitizados[$chave] = $valor;
            }
        }

        return $dadosSanitizados;
    }

    private function cabecalhosSeguros(Request $request): array
    {
        $permitidos = [
            'accept',
            'accept-language',
            'content-type',
            'origin',
            'referer',
            'x-requested-with',
            'x-forwarded-for',
            'x-forwarded-proto',
        ];

        $cabecalhos = [];

        foreach ($permitidos as $cabecalho) {
            $valor = $request->header($cabecalho);

            if ($valor !== null && $valor !== '') {
                $cabecalhos[$cabecalho] = $valor;
            }
        }

        return $cabecalhos;
    }
}
