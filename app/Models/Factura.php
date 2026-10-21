<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Factura extends Model
{
    protected $fillable = [
        'tipo', 'reserva_id', 'huesped_id', 'cliente_nombre', 'cliente_documento', 'subtotal', 'iva', 'total',
        'forma_pago', 'estado', 'motivo_anulacion', 'emitida_por',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $factura) {
            $factura->token ??= Str::random(40);
        });

        static::created(function (self $factura) {
            $factura->updateQuietly(['numero' => sprintf('FV-%06d', $factura->id)]);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(FacturaItem::class);
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function huesped(): BelongsTo
    {
        return $this->belongsTo(Huesped::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function emitidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emitida_por');
    }
}
