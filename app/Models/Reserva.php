<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reserva extends Model
{
    protected $fillable = [
        'huesped_id', 'habitacion_id', 'fecha_entrada', 'fecha_salida', 'adultos', 'ninos',
        'tarifa_noche', 'estado', 'checkin_at', 'checkout_at', 'observaciones', 'creada_por',
    ];

    protected $casts = [
        'fecha_entrada' => 'date',
        'fecha_salida' => 'date',
        'checkin_at' => 'datetime',
        'checkout_at' => 'datetime',
        'tarifa_noche' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (self $reserva) {
            $reserva->updateQuietly(['codigo' => sprintf('RES-%06d', $reserva->id)]);
        });
    }

    public function huesped(): BelongsTo
    {
        return $this->belongsTo(Huesped::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function creadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class)->where('estado', 'emitida');
    }

    public function noches(): int
    {
        return max(1, (int) $this->fecha_entrada->diffInDays($this->fecha_salida));
    }

    public function totalHospedaje(): float
    {
        return $this->noches() * (float) $this->tarifa_noche;
    }

    /** Pedidos del restaurante entregados y cargados a esta estadía que aún no se facturaron. */
    public function consumosPendientes(): HasMany
    {
        return $this->pedidos()
            ->where('forma_pago', 'cargo_habitacion')
            ->where('estado', 'entregado')
            ->whereNull('factura_id');
    }

    /** Reservas que ocupan la habitación en algún día del rango [entrada, salida). */
    public function scopeSeCruzaCon(Builder $query, int $habitacionId, string $entrada, string $salida): Builder
    {
        return $query->where('habitacion_id', $habitacionId)
            ->whereIn('estado', ['reservada', 'en_curso'])
            ->where('fecha_entrada', '<', $salida)
            ->where('fecha_salida', '>', $entrada);
    }
}
