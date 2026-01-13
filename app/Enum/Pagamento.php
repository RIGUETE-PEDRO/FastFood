<?php

namespace App\Enum;

enum Pagamento: int
{

    case CARTAO_CREDITO = 1;
    case CARTAO_DEBITO = 2;
    case PIX = 3;
    case DINHEIRO = 4;


    public static function fromString(string $nome): self
    {
        return match (strtoupper($nome)) {
            'CARTAO_CREDITO' => self::CARTAO_CREDITO,
            'CARTAO_DEBITO' => self::CARTAO_DEBITO,
            'PIX' => self::PIX,
            'DINHEIRO' => self::DINHEIRO,
            default => throw new \InvalidArgumentException("Método de pagamento inválido: $nome")
        };
    }
}
