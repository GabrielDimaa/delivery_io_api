<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxaEntrega extends Model
{
    use HasFactory, TimestampSerializable;

    protected $table = 'taxas_entrega';
    protected $primaryKey = 'id_taxa_entrega';

    public const DESCRICAO_DEFAULT = "Padrão";
    public const VALOR_DEFAULT = 4.50;

    protected $fillable = [
        'descricao',
        'valor',
    ];

    protected $casts = [
        'valor' => 'double'
    ];
}
