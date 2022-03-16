<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    use HasFactory, TimestampSerializable;

    protected $primaryKey = 'id_subcategoria';

    protected $fillable = ['descricao', 'id_categoria'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function produtos()
    {
        return $this->hasMany(Produto::class, 'id_subcategoria', 'id_subcategoria');
    }
}
