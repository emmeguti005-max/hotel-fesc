<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Services\Recepcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->when($vista === 'llegadas', fn ($q) => $q->where('estado', 'reservada')->whereDate('fecha_entrada', '<=', $hoy))
            ->when($vista === 'hospedados', fn ($q) => $q->where('estado', 'en_curso'))
            ->when($vista === 'salidas', fn ($q) => $q->where('estado', 'en_curso')->whereDate('fecha_salida', '<=', $hoy))
            ->when($vista === 'activas', fn ($q) => $q->whereIn('estado', ['reservada', 'en_curso']))
            ->when($vista === 'historial', fn ($q) => $q->whereIn('estado', ['finalizada', 'cancelada']))
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
            ->whereNotIn('estado', ['bloqueada'])
            ->orderBy('numero')
            ->get();

        $huespedes = Huesped::orderBy('nombre')->get(['id', 'nombre', 'tipo_documento', 'documento', 'telefono', 'correo', 'ciudad', 'pais']);
        $habitacionElegida = $request->integer('habitacion_id') ?: null;

        return view('reservas.create', compact('habitaciones', 'huespedes', 'habitacionElegida'));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'huesped.nombre' => ['required', 'string', 'max:120'],
            'huesped.tipo_documento' => ['required', Rule::in(['CC', 'CE', 'PAS', 'NIT', 'TI'])],
            'huesped.documento' => ['required', 'string', 'max:30'],
            'huesped.telefono' => ['nullable', 'string', 'max:30'],
            'huesped.correo' => ['nullable', 'email', 'max:120'],
            'huesped.ciudad' => ['nullable', 'string', 'max:80'],
            'huesped.pais' => ['nullable', 'string', 'max:80'],
            'habitacion_id' => ['required', 'exists:habitaciones,id'],
            'fecha_entrada' => ['required', 'date', 'after_or_equal:today'],
            'fecha_salida' => ['required', 'date', 'after:fecha_entrada'],
            'adultos' => ['required', 'integer', 'between:1,10'],
            'ninos' => ['nullable', 'integer', 'between:0,10'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'checkin_inmediato' => ['nullable', 'boolean'],
        ]);

        $habitacion = Habitacion::findOrFail($datos['habitacion_id']);
        $personas = $datos['adultos'] + ($datos['ninos'] ?? 0);

        if ($personas > $habitacion->capacidad) {
            return back()->withInput()->withErrors(['adultos' => "La habitación {$habitacion->numero} admite máximo {$habitacion->capacidad} personas."]);
        }

        $reserva = DB::transaction(function () use ($datos, $habitacion) {
            if (Reserva::seCruzaCon($habitacion->id, $datos['fecha_entrada'], $datos['fecha_salida'])->lockForUpdate()->exists()) {
                return null;
            }

            $huesped = Huesped::updateOrCreate(
                ['documento' => $datos['huesped']['documento']],
                array_filter($datos['huesped'], fn ($v) => $v !== null) + ['pais' => 'Colombia'],
            );

            $reserva = Reserva::create([
                'huesped_id' => $huesped->id,
                'habitacion_id' => $habitacion->id,
                'fecha_entrada' => $datos['fecha_entrada'],
                'fecha_salida' => $datos['fecha_salida'],
                'adultos' => $datos['adultos'],
                'ninos' => $datos['ninos'] ?? 0,
                'tarifa_noche' => $habitacion->tarifa_noche,
                'observaciones' => $datos['observaciones'] ?? null,
                'creada_por' => Auth::id(),
            ]);

            Auditoria::registrar('crear_reserva', 'reservas', $reserva->id, "{$reserva->fresh()->codigo} · {$huesped->nombre}");

            return $reserva;
        });

        if (! $reserva) {
            return back()->withInput()->withErrors(['habitacion_id' => "La habitación {$habitacion->numero} ya está reservada en esas fechas."]);
        }

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
        $iva = round($subtotal * config('hotel.iva') / 100, 2);
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
            ->with(['reservas' => fn ($q) => $q->where('estado', 'en_curso')->with('habitacion')])
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
