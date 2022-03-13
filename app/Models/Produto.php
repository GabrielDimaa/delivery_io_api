<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria', 'id_subcategoria');
    }
}
