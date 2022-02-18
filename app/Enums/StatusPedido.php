<?php

namespace App\Enums;

enum StatusPedido : int
{
    case EmAberto = 1;
    case Aceito = 2;
    case EmRotaDeEntrega = 3;
    case ProntoParaRetirada = 4;
    case Finalizado = 5;
    case Cancelado = 6;
}
