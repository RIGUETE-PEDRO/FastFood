<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditoriaRegistroService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegistrarAuditoria
{
    public function __construct(private AuditoriaRegistroService $auditoriaRegistroService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Registra apenas para usuários autenticados
        if (Auth::check()) {
            $this->registrarRequisicao($request);
        }

        return $response;
    }

    /**
     * Registra a requisição se for uma ação importante
     */
    private function registrarRequisicao(Request $request): void
    {
        $metodo = $request->method();
        $rota = $request->route()?->getName() ?? $request->path();

        if ($this->deveIgnorarRota($request, $rota, $metodo)) {
            return;
        }

        // Registra todos os movimentos do usuário autenticado
        $this->registrarOperacao($request, $rota, $metodo);
    }

    /**
     * Ignora apenas rotas técnicas para reduzir ruído
     */
    private function deveIgnorarRota(Request $request, string $rota, string $metodo): bool
    {
        if ($metodo === 'HEAD') {
            return true;
        }

        // Não auditar chamadas assíncronas de atualização (polling/AJAX)
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
        ];

        foreach ($ignorados as $item) {
            if (str_contains($path, $item) || str_contains($rota, $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registra a operação na auditoria
     */
    private function registrarOperacao(Request $request, string $rota, string $metodo): void
    {
        $acao = $this->mapearAcao($metodo);
        $recurso = $this->extrairRecurso($rota);

        // Dados a registrar na auditoria
        $detalhes = [
            'rota' => $rota,
            'metodo_http' => $metodo,
            'ip' => $request->ip(),
            'path' => $request->path(),
        ];

        // Adiciona parâmetros relevantes (sem dados sensíveis)
        if (in_array($metodo, ['POST', 'PUT', 'PATCH'])) {
            $detalhes['parametros'] = $this->sanitizarDados($request->all());
        }

        try {
            $this->auditoriaRegistroService->registrar($acao, $recurso, $detalhes);
        } catch (\Exception $e) {
            // Log silencioso de erros em auditoria para não quebrar a aplicação
            Log::error('Erro ao registrar auditoria: ' . $e->getMessage());
        }
    }

    /**
     * Mapeia o método HTTP para uma ação
     */
    private function mapearAcao(string $metodo): string
    {
        return match($metodo) {
            'POST' => 'criar',
            'PUT', 'PATCH' => 'atualizar',
            'DELETE' => 'excluir',
            'GET' => 'visualizar',
            default => 'acessar',
        };
    }

    /**
     * Extrai o recurso do nome da rota
     */
    private function extrairRecurso(string $rota): string
    {
        // Converte "produtos.store" para "produto"
        $partes = explode('.', $rota);
        $recurso = $partes[0] ?? 'desconhecido';

        // Remove plurais comuns
        $recurso = rtrim($recurso, 's');

        return ucfirst($recurso);
    }

    /**
     * Remove dados sensíveis antes de registrar
     */
    private function sanitizarDados(array $dados): array
    {
        $chavesSensiveis = [
            'senha', 'password', 'token', 'secret', 'api_key',
            'numero_cartao', 'cvv', 'pin', 'senha_antiga',
        ];

        $dadosSanitizados = [];
        foreach ($dados as $chave => $valor) {
            $chaveLower = strtolower($chave);

            // Verifica se a chave é sensível
            $ehSensivel = false;
            foreach ($chavesSensiveis as $sensivel) {
                if (str_contains($chaveLower, $sensivel)) {
                    $ehSensivel = true;
                    break;
                }
            }

            if ($ehSensivel) {
                $dadosSanitizados[$chave] = '[REDACTED]';
            } elseif (is_array($valor)) {
                $dadosSanitizados[$chave] = $this->sanitizarDados($valor);
            } else {
                $dadosSanitizados[$chave] = $valor;
            }
        }

        return $dadosSanitizados;
    }
}

