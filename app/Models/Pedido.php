<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pedido extends Model
{
    public const ENTREGADO = 'entregado';

    public const CANCELADO = 'cancelado';

    public const CARGO_HABITACION = 'cargo_habitacion';

    /** Flujo de cocina: cada estado sólo puede avanzar al siguiente. */
    public const SIGUIENTE = [
        'recibido' => 'en_preparacion',
        'en_preparacion' => 'listo',
        'listo' => 'despachado',
        'despachado' => self::ENTREGADO,
    ];

    protected $fillable = [
        'origen', 'habitacion_id', 'reserva_id', 'mesa', 'cliente_nombre', 'estado', 'forma_pago', 'notas',
        'total', 'tomado_por', 'preparado_por', 'despachado_por', 'preparacion_at', 'listo_at',
        'despachado_at', 'entregado_at', 'recibido_por', 'factura_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'preparacion_at' => 'datetime',
        'listo_at' => 'datetime',
        'despachado_at' => 'datetime',
        'entregado_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $pedido) {
            $pedido->token ??= Str::random(40);
        });

        static::created(function (self $pedido) {
            $pedido->updateQuietly(['codigo' => sprintf('PED-%06d', $pedido->id)]);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    public function tomadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tomado_por');
    }

    public function preparadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preparado_por');
    }

    public function despachadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'despachado_por');
    }

    public function destino(): string
    {
        return match ($this->origen) {
            'habitacion' => 'Habitación '.($this->habitacion->numero ?? '—'),
            'mesa' => 'Mesa '.($this->mesa ?? '—'),
            default => 'Mostrador'.($this->cliente_nombre ? ' · '.$this->cliente_nombre : ''),
        };
    }

    public function estaActivo(): bool
    {
        return ! in_array($this->estado, [self::ENTREGADO, self::CANCELADO], true);
    }
}
