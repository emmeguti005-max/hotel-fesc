<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Piso;
use App\Models\Solicitud;
use App\Models\Torre;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(Request $request): View
    {
        $solicitudes = $this->filtradas($request)
            ->orderByRaw("FIELD(prioridad, 'urgente', 'alta', 'media', 'baja')")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $torres = Torre::orderBy('nombre')->get();
        $pisos = Piso::orderBy('numero')->get();
        $vencidas = Solicitud::vencidas()->count();

        return view('panel.index', compact('solicitudes', 'torres', 'pisos', 'vencidas'));
    }

    public function exportar(Request $request): Response
    {
        $solicitudes = $this->filtradas($request)->latest()->get();

        $filas = [['ID', 'Prioridad', 'Estado', 'Torre', 'Piso', 'Área', 'Ubicación', 'Equipo', 'Tipo', 'Descripción', 'Reportado por', 'Técnico', 'Reportado', 'Cerrado']];

        foreach ($solicitudes as $s) {
            $filas[] = [
                $s->id,
                $s->prioridad,
                $s->estado,
                $s->ubicacion->area->piso->torre->nombre,
                $s->ubicacion->area->piso->numero == -1 ? 'Sótano' : $s->ubicacion->area->piso->numero,
                $s->ubicacion->area->nombre,
                $s->ubicacion->nombre,
                $s->equipo->nombre ?? '',
                $s->tipo,
                $s->descripcion,
                $s->nombre_solicitante,
                $s->tecnico->nombre ?? '',
                $s->created_at->format('d/m/Y H:i'),
                $s->fecha_cierre?->format('d/m/Y H:i'),
            ];
        }

        $csv = collect($filas)->map(fn ($fila) => collect($fila)->map(
            fn ($valor) => '"'.str_replace('"', '""', (string) $valor).'"'
        )->implode(','))->implode("\r\n");

        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="solicitudes-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    private function filtradas(Request $request): Builder
    {
        return Solicitud::query()
            ->with(['ubicacion.area.piso.torre', 'equipo', 'tecnico'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('prioridad'), fn ($q) => $q->where('prioridad', $request->string('prioridad')))
            ->when($request->filled('torre_id'), fn ($q) => $q->whereHas(
                'ubicacion.area.piso',
                fn ($q) => $q->where('torre_id', $request->integer('torre_id'))
            ))
            ->when($request->filled('piso_id'), fn ($q) => $q->whereHas(
                'ubicacion.area',
                fn ($q) => $q->where('piso_id', $request->integer('piso_id'))
            ))
            ->when($request->boolean('vencidas'), fn ($q) => $q->vencidas());
    }

    public function show(Solicitud $solicitud): View
    {
        $solicitud->load(['ubicacion.area.piso.torre', 'equipo', 'tecnico', 'seguimientos.usuario', 'evidencias']);

        $hostnameOrigen = $solicitud->ip_origen
            ? Cache::remember("hostname:{$solicitud->ip_origen}", now()->addDay(), function () use ($solicitud) {
                $resuelto = @gethostbyaddr($solicitud->ip_origen);

                return ($resuelto && $resuelto !== $solicitud->ip_origen) ? $resuelto : null;
            })
            : null;

        return view('panel.show', compact('solicitud', 'hostnameOrigen'));
    }

    public function tomar(Solicitud $solicitud, Request $request): RedirectResponse
    {
        if ($solicitud->estado !== 'pendiente') {
            return back()->withErrors(['estado' => 'Esta solicitud ya fue tomada.']);
        }

        $solicitud->update([
            'estado' => 'en_proceso',
            'tecnico_id' => Auth::id(),
            'fecha_asignacion' => now(),
        ]);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'tomar_solicitud',
            'entidad' => 'solicitudes',
            'entidad_id' => $solicitud->id,
            'ip' => $request->ip(),
            'fecha' => now(),
        ]);

        return back()->with('status', 'Solicitud asignada a ti.');
    }

    public function comentar(Solicitud $solicitud, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'comentario' => ['required', 'string', 'max:2000'],
        ]);

        $solicitud->seguimientos()->create([
            'usuario_id' => Auth::id(),
            'comentario' => $validated['comentario'],
        ]);

        return back()->with('status', 'Comentario agregado.');
    }

    public function cerrar(Solicitud $solicitud, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:resuelto,no_resuelto'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! in_array($solicitud->estado, ['pendiente', 'en_proceso'], true)) {
            return back()->withErrors(['estado' => 'Esta solicitud ya está cerrada.']);
        }

        $solicitud->update([
            'estado' => $validated['estado'],
            'tecnico_id' => $solicitud->tecnico_id ?? Auth::id(),
            'fecha_cierre' => now(),
        ]);

        if (! empty($validated['comentario'])) {
            $solicitud->seguimientos()->create([
                'usuario_id' => Auth::id(),
                'comentario' => $validated['comentario'],
            ]);
        }

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'cerrar_solicitud',
            'entidad' => 'solicitudes',
            'entidad_id' => $solicitud->id,
            'detalle' => $validated['estado'],
            'ip' => $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('panel.show', $solicitud)->with('status', 'Solicitud cerrada.');
    }
}
