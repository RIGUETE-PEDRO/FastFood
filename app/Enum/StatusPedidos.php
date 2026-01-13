<?php

namespace App\Enum;

enum StatusPedidos: int
{
    case PENDENTE = 1;
    case EM_PREPARO = 2;
    case A_CAMINHO = 3;
    case ENTREGUE = 4;
    case CANCELADO = 5;



   
}
