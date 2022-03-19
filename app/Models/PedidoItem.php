<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoItem extends Model
{
    use HasFactory, TimestampSerializable;

    protected $table = 'pedido_itens';
    protected $primaryKey = 'id_pedido_item';
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            $query->with('complementoItens');
        });
    }

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

    public function complementoItens(): HasMany
    {
        return $this->hasMany(PedidoComplementoItem::class, 'id_pedido_item', 'id_pedido_item');
    }
}
