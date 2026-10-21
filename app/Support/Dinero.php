<?php

namespace App\Support;

class Dinero
{
    /** Formato de pesos colombianos sin decimales: 120000 → "$ 120.000". */
    public static function cop(float|int|string|null $valor): string
    {
        return '$ '.number_format((float) $valor, 0, ',', '.');
    }
}
