<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Zona común del hotel (lobby, restaurante, cocina, lavandería...). Las habitaciones van aparte. */
class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = ['area_id', 'nombre', 'tipo'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }
}
