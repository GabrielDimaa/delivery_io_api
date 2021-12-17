<?php

namespace App\Models;

use App\Http\Resources\CategoriaResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_categoria';

    protected $fillable = ['descricao'];

    public function index()
    {
        $categorias = Categoria::all();

        $response = [
            'success' => true,
            'data' => $categorias,
        ];

        return response()->json($response);
    }
}
