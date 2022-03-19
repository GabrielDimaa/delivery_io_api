<?php

namespace App\Models;

use App\Traits\TimestampSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoStatus extends Model
{
    use HasFactory, TimestampSerializable;

    protected $table = 'pedido_status';
    protected $primaryKey = 'id_pedido_status';

    protected $fillable = [
        'id_pedido',
        'status'
    ];
}
