<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected static function booted(): void
    {
        static::creating(function (self $solicitud) {
            $solicitud->token ??= Str::random(40);
        });
    }

    /** Horas máximas de respuesta según prioridad, antes de considerarse atrasada. */
    public const SLA_HORAS = [
        'urgente' => 2,
        'alta' => 8,
        'media' => 24,
        'baja' => 72,
    ];

    protected $fillable = [
        'equipo_id', 'ubicacion_id', 'habitacion_id', 'nombre_solicitante', 'tipo', 'descripcion',
        'prioridad', 'estado', 'tecnico_id', 'fecha_asignacion', 'fecha_cierre', 'ip_origen',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
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

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(Seguimiento::class);
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class);
    }

    /** Solicitudes abiertas que superaron el SLA de su prioridad. */
    public function scopeVencidas(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pendiente', 'en_proceso'])
            ->where(function (Builder $q) {
                foreach (self::SLA_HORAS as $prioridad => $horas) {
                    $q->orWhere(function (Builder $q) use ($prioridad, $horas) {
                        $q->where('prioridad', $prioridad)
                            ->where('created_at', '<', now()->subHours($horas));
                    });
                }
            });
    }

    public function estaVencida(): bool
    {
        if (! in_array($this->estado, ['pendiente', 'en_proceso'], true)) {
            return false;
        }

        $horas = self::SLA_HORAS[$this->prioridad] ?? null;

        return $horas !== null && $this->created_at->lt(now()->subHours($horas));
    }
}
