<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Huesped extends Model
{
    protected $table = 'huespedes';

    protected $fillable = ['nombre', 'tipo_documento', 'documento', 'telefono', 'correo', 'ciudad', 'pais'];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
