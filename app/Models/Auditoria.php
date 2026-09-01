<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    public $timestamps = false;

    protected $fillable = ['usuario_id', 'accion', 'entidad', 'entidad_id', 'detalle', 'ip', 'fecha'];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public static function registrar(string $accion, string $entidad, ?int $entidadId = null, ?string $detalle = null): self
    {
        return static::create([
            'usuario_id' => Auth::id(),
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'detalle' => $detalle,
            'ip' => request()->ip(),
            'fecha' => now(),
        ]);
    }
}
