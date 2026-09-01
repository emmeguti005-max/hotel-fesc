<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Torre extends Model
{
    protected $fillable = ['nombre'];

    public function pisos(): HasMany
    {
        return $this->hasMany(Piso::class);
    }
}
