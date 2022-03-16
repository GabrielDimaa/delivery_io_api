<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoComplementoItem extends Model
{
    use HasFactory, TimestampSerializable;

    protected $table = 'pedido_complemento_itens';
    protected $primaryKey = 'id_pedido_item';

    protected $fillable = [
        'id_pedido_item',
        'id_pedido',
        'id_complemento',
        'descricao',
        'valor_unitario',
        'valor_total',
        'quantidade',
        'descricao_categoria'
    ];

    protected $casts = [
        'valor_unitario' => 'double',
        'valor_total' => 'double',
        'quantidade' => 'double'
    ];
}
