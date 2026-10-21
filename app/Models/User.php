<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'correo',
        'password',
        'rol',
        'cargo',
        'telefono',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /** El administrador tiene acceso a todas las áreas. */
    public function tieneRol(string ...$roles): bool
    {
        return $this->isAdmin() || in_array($this->rol, $roles, true);
    }

    /** Pantalla a la que llega cada rol al iniciar sesión. */
    public function rutaInicio(): string
    {
        return match ($this->rol) {
            'aseo' => route('panel.aseo.index'),
            'restaurante' => route('panel.pedidos.index'),
            'mantenimiento' => route('panel.mantenimiento.index'),
            default => route('panel.dashboard'),
        };
    }

    public function solicitudesAsignadas(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'tecnico_id');
    }

    public function tareasAseo(): HasMany
    {
        return $this->hasMany(TareaAseo::class, 'asignada_a');
    }

    public function pedidosDespachados(): HasMany
    {
        return $this->hasMany(Pedido::class, 'despachado_por');
    }
}
