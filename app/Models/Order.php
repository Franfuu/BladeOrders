<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'numero_pedido',
        'fecha',
        'estado',
        'total'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Relación: Un pedido pertenece a un cliente (N:1)
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
