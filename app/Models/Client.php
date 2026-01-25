<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Campos habilitados para asignación masiva
    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'direccion'
    ];

    /**
     * Relación: Un cliente tiene muchos pedidos (1:N)
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
