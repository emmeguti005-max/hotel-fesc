<?php

namespace Tests\Unit;

use App\Support\Dinero;
use Tests\TestCase;

/**
 * Ejemplo de TDD: esta prueba se escribio antes de extraer Dinero::iva()
 * (ver Facturador y ReservaController, donde la formula estaba duplicada)
 * y fijo el comportamiento de redondeo que el metodo debia cumplir.
 */
class DineroTest extends TestCase
{
    public function test_iva_redondea_a_dos_decimales(): void
    {
        config(['hotel.iva' => 19]);

        $this->assertSame(19.0, Dinero::iva(100));
        $this->assertSame(28.51, Dinero::iva(150.05));
    }

    public function test_iva_usa_el_porcentaje_configurado(): void
    {
        config(['hotel.iva' => 0]);

        $this->assertSame(0.0, Dinero::iva(250000));
    }

    public function test_cop_formatea_pesos_colombianos_sin_decimales(): void
    {
        $this->assertSame('$ 120.000', Dinero::cop(120000));
        $this->assertSame('$ 0', Dinero::cop(null));
    }
}
