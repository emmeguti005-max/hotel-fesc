<?php

namespace App\Models;

use App\Support\Etiquetas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piso extends Model
{
    protected $fillable = ['torre_id', 'numero', 'plano_imagen'];

    public function torre(): BelongsTo
    {
        return $this->belongsTo(Torre::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class);
    }

    public function etiqueta(): string
    {
        return $this->numero == -1
            ? 'Sótano'
            : 'Torre '.$this->torre->nombre.' · '.Etiquetas::piso($this->numero);
    }
}
