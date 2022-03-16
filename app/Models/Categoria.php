<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory, TimestampSerializable;

    protected $primaryKey = 'id_categoria';

    protected $fillable = ['descricao'];

    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class, 'id_categoria', 'id_categoria');
    }

    public function complementos()
    {
        return $this->hasMany(Complemento::class, 'id_categoria', 'id_categoria');
    }
}
