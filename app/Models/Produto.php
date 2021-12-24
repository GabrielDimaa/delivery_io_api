<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_produto';

    protected $fillable = [
        'descricao',
        'id_subcategoria',
        'preco',
        'sobre',
        'ativo'
    ];

    public function subcategoria()
    {
        return $this::belongsTo(Subcategoria::class, 'id_subcategoria', 'id_subcategoria');
    }
}
