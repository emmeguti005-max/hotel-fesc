<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->enum('categoria', ['desayuno', 'entrada', 'plato_fuerte', 'bebida', 'postre', 'snack']);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('precio', 12, 2);
            $table->boolean('disponible')->default(true);
            $table->timestamps();
        });

        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->nullable()->unique(); // FV-000001, se asigna al crear
            $table->string('token', 40)->unique(); // URL pública del QR
            $table->enum('tipo', ['hospedaje', 'restaurante']);
            $table->foreignId('reserva_id')->nullable()->constrained('reservas');
            $table->foreignId('huesped_id')->nullable()->constrained('huespedes');
            $table->string('cliente_nombre', 120);
            $table->string('cliente_documento', 40)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva', 12, 2);
            $table->decimal('total', 12, 2);
            $table->enum('forma_pago', ['efectivo', 'tarjeta', 'transferencia', 'cargo_habitacion']);
            $table->enum('estado', ['emitida', 'anulada'])->default('emitida');
            $table->string('motivo_anulacion', 255)->nullable();
            $table->foreignId('emitida_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('factura_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('concepto', 200);
            $table->decimal('cantidad', 8, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->nullable()->unique(); // PED-000001, se asigna al crear
            $table->string('token', 40)->unique(); // URL pública del registro de despacho
            $table->enum('origen', ['habitacion', 'mesa', 'mostrador']);
            $table->foreignId('habitacion_id')->nullable()->constrained('habitaciones');
            $table->foreignId('reserva_id')->nullable()->constrained('reservas'); // estadía a la que se carga
            $table->string('mesa', 20)->nullable();
            $table->string('cliente_nombre', 120)->nullable();
            $table->enum('estado', ['recibido', 'en_preparacion', 'listo', 'despachado', 'entregado', 'cancelado'])->default('recibido');
            $table->enum('forma_pago', ['cargo_habitacion', 'efectivo', 'tarjeta', 'transferencia']);
            $table->text('notas')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('tomado_por')->nullable()->constrained('usuarios')->nullOnDelete(); // NULL = lo pidió el huésped por QR
            $table->foreignId('preparado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('despachado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('preparacion_at')->nullable();
            $table->timestamp('listo_at')->nullable();
            $table->timestamp('despachado_at')->nullable();
            $table->timestamp('entregado_at')->nullable();
            $table->string('recibido_por', 120)->nullable(); // quién firmó/recibió en la entrega
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
            $table->timestamps();

            $table->index('estado');
        });

        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('descripcion', 120); // nombre del producto al momento del pedido
            $table->unsignedSmallInteger('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('factura_items');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('productos');
    }
};
