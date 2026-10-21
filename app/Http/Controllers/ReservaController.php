<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservaRequest;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Services\Recepcion;
use App\Support\Dinero;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function __construct(private Recepcion $recepcion) {}

    public function index(Request $request): View
    {
        $vista = $request->input('vista', 'activas');
        $hoy = today();

        $reservas = Reserva::query()
            ->with(['huesped', 'habitacion'])
            ->when($vista === 'llegadas', fn ($q) => $q->where('estado', Reserva::RESERVADA)->whereDate('fecha_entrada', '<=', $hoy))
            ->when($vista === 'hospedados', fn ($q) => $q->where('estado', Reserva::EN_CURSO))
            ->when($vista === 'salidas', fn ($q) => $q->where('estado', Reserva::EN_CURSO)->whereDate('fecha_salida', '<=', $hoy))
            ->when($vista === 'activas', fn ($q) => $q->whereIn('estado', [Reserva::RESERVADA, Reserva::EN_CURSO]))
            ->when($vista === 'historial', fn ($q) => $q->whereIn('estado', [Reserva::FINALIZADA, Reserva::CANCELADA]))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%'.$request->string('q').'%';
                $q->where(fn ($q) => $q->where('codigo', 'like', $texto)
                    ->orWhereHas('huesped', fn ($q) => $q->where('nombre', 'like', $texto)->orWhere('documento', 'like', $texto))
                    ->orWhereHas('habitacion', fn ($q) => $q->where('numero', 'like', $texto)));
            })
            ->orderBy($vista === 'historial' ? 'updated_at' : 'fecha_entrada', $vista === 'historial' ? 'desc' : 'asc')
            ->paginate(25)
            ->withQueryString();

        return view('reservas.index', compact('reservas', 'vista'));
    }

    public function create(Request $request): View
    {
        $habitaciones = Habitacion::with('piso.torre')
            ->whereNotIn('estado', [Habitacion::BLOQUEADA])
            ->orderBy('numero')
            ->get();

        $huespedes = Huesped::orderBy('nombre')->get(['id', 'nombre', 'tipo_documento', 'documento', 'telefono', 'correo', 'ciudad', 'pais']);
        $habitacionElegida = $request->integer('habitacion_id') ?: null;

        return view('reservas.create', compact('habitaciones', 'huespedes', 'habitacionElegida'));
    }

    public function store(StoreReservaRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $habitacion = Habitacion::findOrFail($datos['habitacion_id']);

        $reserva = $this->recepcion->crear($habitacion, $datos['huesped'], $datos, Auth::id());

        if (! empty($datos['checkin_inmediato']) && $reserva->fecha_entrada->isToday()) {
            $this->recepcion->checkIn($reserva, Auth::id());

            return redirect()->route('panel.reservas.show', $reserva)->with('status', 'Reserva creada y check-in realizado.');
        }

        return redirect()->route('panel.reservas.show', $reserva)->with('status', 'Reserva creada.');
    }

    public function show(Reserva $reserva): View
    {
        $reserva->load(['huesped', 'habitacion.piso.torre', 'creadaPor', 'pedidos.items', 'factura']);

        $consumos = $reserva->consumosPendientes()->get();
        $subtotal = $reserva->totalHospedaje() + $consumos->sum('total');
        $iva = Dinero::iva($subtotal);
        $estimado = ['subtotal' => $subtotal, 'iva' => $iva, 'total' => $subtotal + $iva, 'consumos' => $consumos->sum('total')];

        return view('reservas.show', compact('reserva', 'estimado'));
    }

    public function checkin(Reserva $reserva): RedirectResponse
    {
        $this->recepcion->checkIn($reserva, Auth::id());

        return back()->with('status', 'Check-in realizado. La habitación quedó ocupada.');
    }

    public function checkout(Reserva $reserva, Request $request): RedirectResponse
    {
        $formaPago = $request->validate([
            'forma_pago' => ['required', Rule::in(['efectivo', 'tarjeta', 'transferencia'])],
        ])['forma_pago'];

        $factura = $this->recepcion->checkOut($reserva, $formaPago, Auth::id());

        return redirect()->route('panel.facturas.show', $factura)
            ->with('status', 'Check-out realizado. Se emitió la factura y la habitación pasó a aseo.');
    }

    public function cancelar(Reserva $reserva): RedirectResponse
    {
        $this->recepcion->cancelar($reserva);

        return back()->with('status', 'Reserva cancelada.');
    }

    public function huespedes(Request $request): View
    {
        $huespedes = Huesped::query()
            ->withCount('reservas')
            ->with(['reservas' => fn ($q) => $q->where('estado', Reserva::EN_CURSO)->with('habitacion')])
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%'.$request->string('q').'%';
                $q->where(fn ($q) => $q->where('nombre', 'like', $texto)->orWhere('documento', 'like', $texto));
            })
            ->orderBy('nombre')
            ->paginate(30)
            ->withQueryString();

        return view('reservas.huespedes', compact('huespedes'));
    }
}
