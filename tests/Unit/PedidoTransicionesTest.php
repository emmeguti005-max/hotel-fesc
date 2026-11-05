<?php

namespace Tests\Unit;

use App\Models\Factura;
use App\Models\Pedido;
use App\Models\User;
use App\Services\Cocina;
use App\Services\Facturador;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * CP-34. Tabla de transiciones de Pedido::SIGUIENTE. Facturador se reemplaza
 * por un doble: aqui importa el cambio de estado, no como se calcula la factura.
 */
class PedidoTransicionesTest extends TestCase
{
    use RefreshDatabase;

    private Cocina $cocina;

    private MockInterface $facturador;

    protected function setUp(): void
    {
        parent::setUp();

        // Cocina registra cada paso en la auditoria, que exige un usuario.
        $this->actingAs(User::factory()->create());

        $this->facturador = Mockery::mock(Facturador::class);
        $this->cocina = new Cocina($this->facturador);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function pedidoEn(string $estado, string $formaPago = Pedido::CARGO_HABITACION): Pedido
    {
        return Pedido::create([
            'origen' => 'mostrador',
            'estado' => $estado,
            'forma_pago' => $formaPago,
            'total' => 15000,
        ]);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function transicionesValidas(): array
    {
        return [
            'recibido pasa a preparacion' => ['recibido', 'en_preparacion'],
            'preparacion pasa a listo' => ['en_preparacion', 'listo'],
            'listo pasa a despachado' => ['listo', 'despachado'],
            'despachado pasa a entregado' => ['despachado', 'entregado'],
        ];
    }

    #[DataProvider('transicionesValidas')]
    public function test_cada_estado_avanza_solo_al_siguiente(string $actual, string $esperado): void
    {
        $this->facturador->shouldNotReceive('facturarPedido');

        $pedido = $this->cocina->avanzar($this->pedidoEn($actual));

        $this->assertSame($esperado, $pedido->estado);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function estadosCerrados(): array
    {
        return [
            'entregado' => [Pedido::ENTREGADO],
            'cancelado' => [Pedido::CANCELADO],
        ];
    }

    #[DataProvider('estadosCerrados')]
    public function test_un_pedido_cerrado_no_puede_avanzar(string $estado): void
    {
        $this->expectException(DomainException::class);

        $this->cocina->avanzar($this->pedidoEn($estado));
    }

    public function test_al_entregar_un_pedido_pagado_en_el_momento_se_emite_su_factura(): void
    {
        $this->facturador->shouldReceive('facturarPedido')->once()->andReturn(new Factura);

        $pedido = $this->cocina->avanzar($this->pedidoEn('despachado', 'efectivo'));

        $this->assertSame(Pedido::ENTREGADO, $pedido->estado);
    }
}
