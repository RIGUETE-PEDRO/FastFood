<?php

namespace App\Enum;

enum StatusPedidos: int
{
    case PENDENTE = 1;
    case EM_PREPARO = 2;
    case A_CAMINHO = 3;
    case ENTREGUE = 4;
    case CANCELADO = 5;

    /**
     * Retorna o rótulo amigável (Label)
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::PENDENTE   => 'Pendente',
            self::EM_PREPARO => 'Em preparo',
            self::A_CAMINHO  => 'A caminho',
            self::ENTREGUE   => 'Entregue',
            self::CANCELADO  => 'Cancelado',
        };
    }

    /**
     * Define a máquina de estados (o fluxo do pedido)
     */
    public function proximo(): ?self
    {
        return match ($this) {
            self::PENDENTE   => self::EM_PREPARO,
            self::EM_PREPARO => self::A_CAMINHO,
            self::A_CAMINHO  => self::ENTREGUE,
            default          => null,
        };
    }

}
