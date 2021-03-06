<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complemento extends Model
{
    use HasFactory, TimestampSerializable;

    protected $primaryKey = 'id_complemento';

    protected $fillable = [
        'descricao',
        'id_categoria',
        'preco'
    ];

    protected $casts = [
        'preco' => 'double'
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }
}
