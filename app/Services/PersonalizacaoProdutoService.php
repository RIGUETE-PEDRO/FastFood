<?php

namespace App\Services;

use App\Models\ProdutoModel;
use App\Models\BordaModel;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PersonalizacaoProdutoService
{
    private const TAMANHOS = ['P', 'M', 'G', 'GG', 'XG'];
    public const PRECOS_PADRAO = ['P' => 56.00, 'M' => 66.00, 'G' => 76.00, 'GG' => 86.00, 'XG' => 100.00];
    public const FATIAS_POR_TAMANHO = ['P' => 4, 'M' => 6, 'G' => 8, 'GG' => 10, 'XG' => 12];

    /**
     * Valida uma configuração com dados atuais do catálogo e calcula seu preço.
     * Nenhum preço enviado pelo navegador participa do cálculo.
     *
     * @return array{personalizacao: ?array, preco_unitario: float}
     */
    public function cotar(ProdutoModel $produto, mixed $configuracao): array
    {
        if (!$produto->disponivel) {
            throw new InvalidArgumentException('Este produto está indisponível no momento.');
        }

        if (is_string($configuracao)) {
            $configuracao = json_decode($configuracao, true);
        }

        if (!is_array($configuracao)) {
            $configuracao = [];
        }
        $configuracao = array_filter($configuracao, static fn ($valor) => $valor !== null && $valor !== '');

        $categoria = $this->normalizar((string) optional($produto->categoria)->nome);

        if (in_array($categoria, ['pizza', 'pizzas'], true)) {
            return $this->cotarPizza($produto, $configuracao);
        }

        if (in_array($categoria, ['porcao', 'porcoes'], true)) {
            return $this->cotarPorcao($produto, $configuracao);
        }

        if (!empty($configuracao)) {
            throw new InvalidArgumentException('Este produto não aceita essa personalização.');
        }

        return [
            'personalizacao' => null,
            'preco_unitario' => $this->preco($produto->preco),
        ];
    }

    private function cotarPizza(ProdutoModel $sabor1, array $configuracao): array
    {
        if (isset($configuracao['tipo']) && $configuracao['tipo'] !== 'pizza') {
            throw new InvalidArgumentException('Esta pizza não aceita essa personalização.');
        }

        $modo = $configuracao['modo'] ?? 'inteira';
        if (!in_array($modo, ['inteira', 'meia_a_meia'], true)) {
            throw new InvalidArgumentException('Escolha uma opção válida para a pizza.');
        }

        $tamanho = strtoupper(trim((string) ($configuracao['tamanho'] ?? 'P')));
        if (!in_array($tamanho, self::TAMANHOS, true)) {
            throw new InvalidArgumentException('O tamanho escolhido não é válido.');
        }

        $precos1 = $this->precosPorTamanho($sabor1);
        $sabor2 = null;

        if ($modo === 'meia_a_meia') {
            $sabor2Id = $configuracao['sabor_2_id'] ?? data_get($configuracao, 'sabor_2.id');
            if (!filter_var($sabor2Id, FILTER_VALIDATE_INT) || (int) $sabor2Id < 1) {
                throw new InvalidArgumentException('Escolha o segundo sabor da pizza.');
            }
            if ((int) $sabor2Id === (int) $sabor1->id) {
                throw new InvalidArgumentException('Escolha dois sabores diferentes para a pizza meia a meia.');
            }

            $sabor2 = ProdutoModel::with('categoria')
                ->whereKey((int) $sabor2Id)
                ->where('disponivel', true)
                ->first();

            if (!$sabor2 || !in_array($this->normalizar((string) optional($sabor2->categoria)->nome), ['pizza', 'pizzas'], true)) {
                throw new InvalidArgumentException('Os dois sabores devem pertencer à categoria de pizzas e estar disponíveis.');
            }

            $precos2 = $this->precosPorTamanho($sabor2);
            if (!isset($precos1[$tamanho], $precos2[$tamanho])) {
                throw new InvalidArgumentException('Selecione um tamanho cadastrado para os dois sabores.');
            }
            $borda = $this->bordaSelecionada($configuracao);
            $preco1 = $this->preco($precos1[$tamanho]);
            $preco2 = $this->preco($precos2[$tamanho]);
            $preco = max($preco1, $preco2) + $this->adicionalBorda($tamanho, $borda);

            return [
                'personalizacao' => [
                    'tipo' => 'pizza',
                    'modo' => 'meia_a_meia',
                    'tamanho' => $tamanho,
                    'sabor_1' => ['id' => (int) $sabor1->id, 'nome' => (string) $sabor1->nome],
                    'sabor_2' => ['id' => (int) $sabor2->id, 'nome' => (string) $sabor2->nome],
                    'borda_id' => $borda?->id,
                    'borda' => $borda?->nome ?? 'Sem borda',
                ],
                'preco_unitario' => $preco,
            ];
        }

        if (!isset($precos1[$tamanho])) {
            throw new InvalidArgumentException('Selecione um tamanho cadastrado para esta pizza.');
        }
        $borda = $this->bordaSelecionada($configuracao);
        $preco = $this->preco($precos1[$tamanho]) + $this->adicionalBorda($tamanho, $borda);

        return [
            'personalizacao' => [
                'tipo' => 'pizza',
                'modo' => 'inteira',
                'tamanho' => $tamanho,
                'sabor_1' => ['id' => (int) $sabor1->id, 'nome' => (string) $sabor1->nome],
                'sabor_2' => null,
                'borda_id' => $borda?->id,
                'borda' => $borda?->nome ?? 'Sem borda',
            ],
            'preco_unitario' => $preco,
        ];
    }

    private function cotarPorcao(ProdutoModel $produto, array $configuracao): array
    {
        if (isset($configuracao['tipo']) && $configuracao['tipo'] !== 'porcao') {
            throw new InvalidArgumentException('Esta porção não aceita essa personalização.');
        }

        $tipoPorcao = $configuracao['tipo_porcao'] ?? 'inteira';
        if (!in_array($tipoPorcao, ['inteira', 'meia'], true)) {
            throw new InvalidArgumentException('Escolha meia ou inteira para a porção.');
        }

        if ($tipoPorcao === 'meia' && (!$produto->aceita_meia_por || $produto->preco_meia_por === null)) {
            throw new InvalidArgumentException('Este produto não está configurado para meia porção.');
        }

        return [
            'personalizacao' => [
                'tipo' => 'porcao',
                'tipo_porcao' => $tipoPorcao,
            ],
            'preco_unitario' => $this->preco($tipoPorcao === 'meia' ? $produto->preco_meia_por : $produto->preco),
        ];
    }

    private function precosPorTamanho(ProdutoModel $produto): array
    {
        $precos = is_array($produto->precos_por_tamanho) ? $produto->precos_por_tamanho : [];
        $normalizados = [];

        foreach (self::TAMANHOS as $tamanho) {
            if (isset($precos[$tamanho]) && is_numeric($precos[$tamanho]) && (float) $precos[$tamanho] > 0) {
                $normalizados[$tamanho] = $precos[$tamanho];
            }
        }

        return $normalizados ?: self::PRECOS_PADRAO;
    }

    private function bordaSelecionada(array $configuracao): ?BordaModel
    {
        $bordaId = $configuracao['borda_id'] ?? null;
        $bordaAntiga = mb_strtolower(trim((string) ($configuracao['borda'] ?? '')));

        if ($bordaId === null || $bordaId === '') {
            if ($bordaAntiga === '' || in_array($bordaAntiga, ['sem borda', 'sem_borda'], true)) {
                return null;
            }

            if (in_array($bordaAntiga, ['com borda', 'com_borda'], true)) {
                $bordaId = BordaModel::query()->where('padrao', true)->where('ativa', true)->value('id');
            } elseif (filter_var($bordaAntiga, FILTER_VALIDATE_INT) && (int) $bordaAntiga > 0) {
                $bordaId = $bordaAntiga;
            } else {
                throw new InvalidArgumentException('Escolha uma borda válida.');
            }
        }

        if ((string) $bordaId === '0') {
            return null;
        }

        if (!filter_var($bordaId, FILTER_VALIDATE_INT) || (int) $bordaId < 1) {
            throw new InvalidArgumentException('Escolha uma borda válida.');
        }

        $borda = BordaModel::query()->whereKey((int) $bordaId)->where('ativa', true)->first();
        if (!$borda) {
            throw new InvalidArgumentException('Essa borda não está ativa no momento.');
        }

        return $borda;
    }

    private function adicionalBorda(string $tamanho, ?BordaModel $borda): float
    {
        if (!$borda) {
            return 0.0;
        }

        $adicionais = is_array($borda->adicionais_por_tamanho) ? $borda->adicionais_por_tamanho : [];
        $valor = $adicionais[$tamanho] ?? 0;

        if (!is_numeric($valor) || (float) $valor < 0) {
            throw new InvalidArgumentException('O preço adicional desta borda está inválido.');
        }

        return round((float) $valor, 2);
    }

    private function preco(mixed $valor): float
    {
        if (!is_numeric($valor) || (float) $valor <= 0) {
            throw new InvalidArgumentException('O preço deste produto está inválido.');
        }

        return round((float) $valor, 2);
    }

    private function normalizar(string $valor): string
    {
        return mb_strtolower(Str::ascii(trim($valor)));
    }
}
