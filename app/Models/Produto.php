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
        'id_categoria',
        'id_subcategoria',
        'preco',
        'sobre',
        'ativo'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria', 'id_subcategoria');
    }
}
