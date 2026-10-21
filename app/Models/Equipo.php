<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Activo del hotel. Vive en una habitación (TV, minibar, caja fuerte...) o en una
 * zona común (cocina, lavandería, lobby...): exactamente uno de los dos.
 */
class Equipo extends Model
{
    protected $fillable = [
        'ubicacion_id', 'habitacion_id', 'tipo', 'codigo', 'nombre', 'usuario_asignado',
        'marca', 'modelo', 'serie', 'estado', 'pos_x', 'pos_y',
    ];

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    /** Historial de solicitudes resueltas: la "hoja de vida" del activo. */
    public function mantenimientos(): HasMany
    {
        return $this->solicitudes()->whereIn('estado', ['resuelto', 'no_resuelto']);
    }

    public function piso(): ?Piso
    {
        return $this->habitacion?->piso ?? $this->ubicacion?->area?->piso;
    }

    public function lugar(): string
    {
        if ($this->habitacion) {
            return $this->habitacion->lugar();
        }

        return $this->ubicacion
            ? $this->ubicacion->area->piso->etiqueta().' · '.$this->ubicacion->area->nombre.' · '.$this->ubicacion->nombre
            : '—';
    }
}
