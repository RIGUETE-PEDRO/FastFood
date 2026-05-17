<?php
namespace App\Enum;

enum TipoUsuario: int
{
    case CLIENTE = 1;
    case ESTABELECIMENTO = 2;
    case ADMINISTRADOR = 3;
    case ENTREGADOR = 4;
    case KEYCLOCK = 5;
    case GARCOM = 6;

    public function label(): string
    {
        return match($this) {
            self::CLIENTE         => 'Cliente',
            self::ESTABELECIMENTO => 'Estabelecimento',
            self::ADMINISTRADOR   => 'Administrador',
            self::ENTREGADOR      => 'Entregador',
            self::KEYCLOCK        => 'keyclock',
            self::GARCOM          => 'garçom',
        };
    }
}
