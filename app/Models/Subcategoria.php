<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_subcategoria';

    protected $fillable = ['descricao', 'id_categoria'];
}
