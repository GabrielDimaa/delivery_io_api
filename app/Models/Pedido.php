<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory, TimestampSerializable;

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
        'status',
        'observacao',
        'finalizado_at',
        'cancelado_at',
        'tempo_estimado',
        'id_taxa_entrega',
        'descricao_taxa_entrega',
        'valor_taxa_entrega',
    ];

    protected $casts = [
        'valor_total' => 'double',
        'troco' => 'double',
        'valor_pago' => 'double',
        'valor_taxa_entrega' => 'double'
    ];

    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'id_pedido', 'id_pedido');
    }
}
