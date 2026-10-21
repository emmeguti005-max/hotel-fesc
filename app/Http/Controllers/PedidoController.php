<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\Cocina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function __construct(private Cocina $cocina) {}

    /** Tablero de cocina: una columna por estado, del pedido recién llegado al despachado. */
    public function index(): View
    {
        $pedidos = Pedido::with(['items', 'habitacion', 'despachadoPor', 'preparadoPor'])
            ->whereIn('estado', ['recibido', 'en_preparacion', 'listo', 'despachado'])
            ->oldest()
            ->get()
            ->groupBy('estado');

        $entregadosHoy = Pedido::where('estado', 'entregado')->whereDate('entregado_at', today())->count();
        $ventasHoy = Pedido::where('estado', 'entregado')->whereDate('entregado_at', today())->sum('total');

        return view('pedidos.index', compact('pedidos', 'entregadosHoy', 'ventasHoy'));
    }

    public function create(Request $request): View
    {
        $productos = Producto::where('disponible', true)->orderBy('categoria')->orderBy('nombre')->get()->groupBy('categoria');
        $ocupadas = Habitacion::with('reservaActual.huesped')->where('estado', 'ocupada')->orderBy('numero')->get();
        $habitacionElegida = $request->integer('habitacion_id') ?: null;

        return view('pedidos.create', compact('productos', 'ocupadas', 'habitacionElegida'));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'origen' => ['required', Rule::in(['habitacion', 'mesa', 'mostrador'])],
            'habitacion_id' => ['required_if:origen,habitacion', 'nullable', 'exists:habitaciones,id'],
            'mesa' => ['required_if:origen,mesa', 'nullable', 'string', 'max:20'],
            'cliente_nombre' => ['nullable', 'string', 'max:120'],
            'forma_pago' => ['required', Rule::in(['cargo_habitacion', 'efectivo', 'tarjeta', 'transferencia'])],
            'notas' => ['nullable', 'string', 'max:500'],
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['nullable', 'integer', 'between:0,50'],
        ]);

        $pedido = $this->cocina->crearPedido($datos, $datos['cantidades'], Auth::id());

        return redirect()->route('panel.pedidos.index')->with('status', "Pedido {$pedido->fresh()->codigo} enviado a cocina.");
    }

    /** Registro de despacho del pedido (imprimible, con QR). */
    public function show(Pedido $pedido): View
    {
        $pedido->load(['items', 'habitacion.piso.torre', 'reserva.huesped', 'tomadoPor', 'preparadoPor', 'despachadoPor', 'factura']);

        return view('pedidos.show', compact('pedido'));
    }

    public function historial(Request $request): View
    {
        $pedidos = Pedido::with(['habitacion', 'despachadoPor', 'factura'])
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('fecha'), fn ($q) => $q->whereDate('created_at', $request->date('fecha')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('pedidos.historial', compact('pedidos'));
    }

    public function avanzar(Pedido $pedido, Request $request): RedirectResponse
    {
        $datos = $request->validate(['recibido_por' => ['nullable', 'string', 'max:120']]);
        $pedido = $this->cocina->avanzar($pedido, Auth::id(), $datos['recibido_por'] ?? null);

        $mensaje = match ($pedido->estado) {
            'en_preparacion' => "{$pedido->codigo} en preparación.",
            'listo' => "{$pedido->codigo} listo para despachar.",
            'despachado' => "{$pedido->codigo} despachado. Quedó registrado quién lo llevó.",
            'entregado' => "{$pedido->codigo} entregado.".($pedido->factura_id ? ' Se emitió su factura.' : ' Cargado a la cuenta de la habitación.'),
            default => 'Pedido actualizado.',
        };

        return back()->with('status', $mensaje);
    }

    public function cancelar(Pedido $pedido): RedirectResponse
    {
        $this->cocina->cancelar($pedido);

        return back()->with('status', "{$pedido->codigo} cancelado.");
    }
}
