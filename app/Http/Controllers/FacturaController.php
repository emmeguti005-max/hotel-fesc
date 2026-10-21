<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Factura;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacturaController extends Controller
{
    public function index(Request $request): View
    {
        $facturas = Factura::query()
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('hasta')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%'.$request->string('q').'%';
                $q->where(fn ($q) => $q->where('numero', 'like', $texto)
                    ->orWhere('cliente_nombre', 'like', $texto)
                    ->orWhere('cliente_documento', 'like', $texto));
            });

        $totales = (clone $facturas)->where('estado', Factura::EMITIDA)->selectRaw('count(*) as cantidad, coalesce(sum(total), 0) as total')->first();
        $facturas = $facturas->latest()->paginate(25)->withQueryString();

        return view('facturas.index', compact('facturas', 'totales'));
    }

    public function show(Factura $factura): View
    {
        return $this->vista($factura, interna: true);
    }

    /** Versión pública a la que lleva el QR impreso en la factura. */
    public function publica(Factura $factura): View
    {
        return $this->vista($factura, interna: false);
    }

    public function anular(Factura $factura, Request $request): RedirectResponse
    {
        $motivo = $request->validate(['motivo' => ['required', 'string', 'max:255']])['motivo'];

        if ($factura->estado === Factura::ANULADA) {
            return back()->withErrors(['motivo' => 'La factura ya está anulada.']);
        }

        $factura->update(['estado' => Factura::ANULADA, 'motivo_anulacion' => $motivo]);
        $factura->pedidos()->update(['factura_id' => null]);
        Auditoria::registrar('anular_factura', 'facturas', $factura->id, "{$factura->numero}: {$motivo}");

        return back()->with('status', "Factura {$factura->numero} anulada. Sus consumos quedaron pendientes de facturar.");
    }

    private function vista(Factura $factura, bool $interna): View
    {
        $factura->load(['items', 'reserva.habitacion', 'emitidaPor']);

        return view('facturas.show', compact('factura', 'interna'));
    }
}
