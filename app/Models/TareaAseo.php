<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TareaAseo extends Model
{
    protected $table = 'tareas_aseo';

    protected $fillable = [
        'habitacion_id', 'asignada_a', 'tipo', 'prioridad', 'estado', 'notas', 'observaciones',
        'inicio_at', 'fin_at', 'creada_por',
    ];

    protected $casts = [
        'inicio_at' => 'datetime',
        'fin_at' => 'datetime',
    ];

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function asignadaA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignada_a');
    }

    public function creadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    public function minutos(): ?int
    {
        return $this->inicio_at && $this->fin_at ? (int) $this->inicio_at->diffInMinutes($this->fin_at) : null;
    }
}
