<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    protected $fillable = [
        'piso_id', 'numero', 'tipo', 'capacidad', 'tarifa_noche', 'estado', 'descripcion', 'pos_x', 'pos_y',
    ];

    protected $casts = [
        'tarifa_noche' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $habitacion) {
            $habitacion->token ??= Str::random(40);
        });
    }

    public function piso(): BelongsTo
    {
        return $this->belongsTo(Piso::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    /** La estadía activa (huésped hospedado ahora mismo), si la hay. */
    public function reservaActual(): HasOne
    {
        return $this->hasOne(Reserva::class)->where('estado', 'en_curso')->latestOfMany('checkin_at');
    }

    public function tareasAseo(): HasMany
    {
        return $this->hasMany(TareaAseo::class);
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function lugar(): string
    {
        return 'Habitación '.$this->numero.' · '.$this->piso->etiqueta();
    }
}
