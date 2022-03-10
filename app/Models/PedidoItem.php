<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'pedido_itens';
    protected $primaryKey = 'id_pedido_item';

    protected $fillable = [
        'id_pedido',
        'id_produto',
        'descricao',
        'valor_unitario',
        'valor_total',
        'quantidade',
        'descricao_subcategoria'
    ];

    protected $casts = [
        'valor_unitario' => 'double',
        'valor_total' => 'double',
        'quantidade' => 'double'
    ];
}
