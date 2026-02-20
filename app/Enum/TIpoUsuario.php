<?php
namespace App\Enum;

enum TipoUsuario: int
{
    case ADMINISTRADOR = 1;
    case FUNCIONARIO = 2;
    case CLIENTE = 3;
    case ENTREGADOR = 4;

    public function label(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'Administrador',
            self::FUNCIONARIO   => 'Funcionário',
            self::CLIENTE       => 'Cliente',
            self::ENTREGADOR    => 'Entregador',
        };
    }
}
