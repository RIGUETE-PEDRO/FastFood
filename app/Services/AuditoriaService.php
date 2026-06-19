<?php

namespace App\Services;

use App\Models\SecureKeyAuditoriaModel;

class AuditoriaService
{
    /**
     * Registra uma ação de auditoria
     *
     * @param string $acao
     * @param string $recurso
     * @param array $detalhes
     * @param int|null $usuarioId
     * @return SecureKeyAuditoriaModel
     */
    public static function registrar(
        string $acao,
        string $recurso,
        array $detalhes = [],
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return app(AuditoriaRegistroService::class)->registrar($acao, $recurso, $detalhes, $usuarioId);
    }

    /**
     * Registra criação de recurso
     */
    public static function registrarCriacao(
        string $recurso,
        array $dados,
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'criar',
            $recurso,
            ['dados' => $dados],
            $usuarioId
        );
    }

    /**
     * Registra atualização de recurso
     */
    public static function registrarAtualizacao(
        string $recurso,
        array $dadosAntigos,
        array $dadosNovos,
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'atualizar',
            $recurso,
            [
                'dados_antigos' => $dadosAntigos,
                'dados_novos' => $dadosNovos,
                'campos_alterados' => self::camposAlterados($dadosAntigos, $dadosNovos),
            ],
            $usuarioId
        );
    }

    /**
     * Registra exclusão de recurso
     */
    public static function registrarExclusao(
        string $recurso,
        array $dados,
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'excluir',
            $recurso,
            ['dados_deletados' => $dados],
            $usuarioId
        );
    }

    /**
     * Registra visualização de recurso
     */
    public static function registrarVisualizacao(
        string $recurso,
        int $resourceId,
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'visualizar',
            "{$recurso}:{$resourceId}",
            ['recurso_id' => $resourceId],
            $usuarioId
        );
    }

    /**
     * Registra login de usuário
     */
    public static function registrarLogin(
        int $usuarioId,
        string $metodo = 'email'
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'login',
            'autenticacao',
            [
                'metodo' => $metodo,
                'timestamp' => now()->toDateTimeString(),
            ],
            $usuarioId
        );
    }

    /**
     * Registra logout de usuário
     */
    public static function registrarLogout(
        int $usuarioId
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'logout',
            'autenticacao',
            ['timestamp' => now()->toDateTimeString()],
            $usuarioId
        );
    }

    /**
     * Registra alteração de permissões/roles
     */
    public static function registrarAlteracaoPermissoes(
        int $usuarioIdAlvo,
        array $rolesAntigos,
        array $rolesNovos,
        ?int $usuarioIdAdmin = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'alterar_permissoes',
            "usuario:{$usuarioIdAlvo}",
            [
                'roles_removidas' => array_diff($rolesAntigos, $rolesNovos),
                'roles_adicionadas' => array_diff($rolesNovos, $rolesAntigos),
            ],
            $usuarioIdAdmin
        );
    }

    /**
     * Registra ação customizada com contexto completo
     */
    public static function registrarAcaoCustomizada(
        string $acao,
        string $recurso,
        string $descricao,
        array $contexto = [],
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            $acao,
            $recurso,
            [
                'descricao' => $descricao,
                'contexto' => $contexto,
                'timestamp' => now()->toDateTimeString(),
            ],
            $usuarioId
        );
    }

    /**
     * Registra ação de pagamento
     */
    public static function registrarPagamento(
        int $pedidoId,
        string $status,
        array $dadosPagamento,
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'processar_pagamento',
            "pedido:{$pedidoId}",
            [
                'status_pagamento' => $status,
                'dados' => $dadosPagamento,
                'timestamp' => now()->toDateTimeString(),
            ],
            $usuarioId
        );
    }

    /**
     * Registra mudança de status de pedido
     */
    public static function registrarMudancaStatusPedido(
        int $pedidoId,
        string $statusAntigo,
        string $statusNovo,
        string $motivo = '',
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'mudanca_status_pedido',
            "pedido:{$pedidoId}",
            [
                'status_anterior' => $statusAntigo,
                'status_novo' => $statusNovo,
                'motivo' => $motivo,
                'timestamp' => now()->toDateTimeString(),
            ],
            $usuarioId
        );
    }

    /**
     * Registra adição de item ao carrinho
     */
    public static function registrarAdicaoCarrinho(
        int $carrinhoId,
        int $produtoId,
        int $quantidade,
        float $preco,
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'adicionar_carrinho',
            "carrinho:{$carrinhoId}",
            [
                'produto_id' => $produtoId,
                'quantidade' => $quantidade,
                'preco' => $preco,
                'timestamp' => now()->toDateTimeString(),
            ],
            $usuarioId
        );
    }

    /**
     * Registra remoção de item do carrinho
     */
    public static function registrarRemocaoCarrinho(
        int $carrinhoId,
        int $produtoId,
        int $quantidade,
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'remover_carrinho',
            "carrinho:{$carrinhoId}",
            [
                'produto_id' => $produtoId,
                'quantidade' => $quantidade,
                'timestamp' => now()->toDateTimeString(),
            ],
            $usuarioId
        );
    }

    /**
     * Registra tentativa de acesso não autorizado
     */
    public static function registrarAcessoNaoAutorizado(
        string $recurso,
        string $motivo = '',
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'acesso_negado',
            $recurso,
            [
                'motivo' => $motivo,
                'usuario_id' => $usuarioId,
                'timestamp' => now()->toDateTimeString(),
            ],
            $usuarioId
        );
    }

    /**
     * Registra relatório gerado
     */
    public static function registrarRelatorioGerado(
        string $tipoRelatorio,
        array $filtros = [],
        ?int $usuarioId = null
    ): SecureKeyAuditoriaModel {
        return self::registrar(
            'gerar_relatorio',
            $tipoRelatorio,
            [
                'filtros' => $filtros,
                'timestamp' => now()->toDateTimeString(),
            ],
            $usuarioId
        );
    }

    private static function camposAlterados(array $dadosAntigos, array $dadosNovos): array
    {
        $campos = [];

        foreach ($dadosNovos as $campo => $valorNovo) {
            $valorAntigo = $dadosAntigos[$campo] ?? null;

            if (json_encode($valorAntigo) !== json_encode($valorNovo)) {
                $campos[] = $campo;
            }
        }

        return $campos;
    }

}
