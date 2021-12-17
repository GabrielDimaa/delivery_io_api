<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'codigo_pedido',
        'nome',
        'telefone',
        'rua',
        'bairro',
        'numero',
        'cep',
        'cidade',
        'valor_total',
        'troco',
        'valor_pago',
        'forma_pagamento',
        'tipo_entrega',
        'observacao',
        'finalizado_at',
        'tempo_estimado',
        'id_taxa_entrega',
        'descricao_taxa_entrega',
        'valor_taxa_entrega',
    ];
}
