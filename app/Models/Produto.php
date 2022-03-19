<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Produto extends Model
{
    use HasFactory, TimestampSerializable;

    protected $primaryKey = 'id_produto';

    protected $fillable = [
        'descricao',
        'id_categoria',
        'id_subcategoria',
        'preco',
        'sobre',
        'ativo'
    ];

    protected $casts = [
        'preco' => 'double'
    ];

    //Busca os 5 produtos mais vendidos
    public static function getProdutosMaisVendidos(): Collection
    {
        $quantidadeQuery = DB::table('pedido_itens')
            ->select('id_produto', DB::raw('count(*) AS quantidade'))
            ->groupBy('id_produto')
            ->orderBy('quantidade', 'desc')
            ->limit(5);

        return DB::table('produtos')
            ->select('produtos.*', 'produtos_quantidade.quantidade')
            ->joinSub($quantidadeQuery, 'produtos_quantidade', function ($join) {
                $join->on('produtos.id_produto', '=', 'produtos_quantidade.id_produto');
            })->get();
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function subcategoria(): BelongsTo
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria', 'id_subcategoria');
    }
}
