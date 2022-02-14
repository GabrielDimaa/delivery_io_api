<?php

namespace App\Enums;

enum StatusPedido : int {
    case EmAberto = 1;
    case Aceito = 2;
    case EmRotaDeEntrega = 3;
    case Finalizado = 4;
    case Cancelado = 5;
}
