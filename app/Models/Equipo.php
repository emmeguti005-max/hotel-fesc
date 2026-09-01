<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{
    protected $fillable = [
        'ubicacion_id', 'tipo', 'codigo', 'nombre', 'usuario_asignado',
        'marca', 'modelo', 'serie', 'estado', 'pos_x', 'pos_y',
    ];

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    /** Historial de solicitudes resueltas: la "hoja de vida" del equipo. */
    public function mantenimientos(): HasMany
    {
        return $this->solicitudes()->whereIn('estado', ['resuelto', 'no_resuelto']);
    }
}
