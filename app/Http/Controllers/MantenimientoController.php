<?php

namespace App\Http\Controllers;

use App\Http\Requests\CerrarSolicitudRequest;
use App\Http\Requests\ComentarSolicitudRequest;
use App\Models\Auditoria;
use App\Models\Solicitud;
use App\Models\Torre;
use App\Support\Etiquetas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Reportes de daños en habitaciones y zonas comunes, atendidos por el equipo de mantenimiento. */
class MantenimientoController extends Controller
{
    public function index(Request $request): View
    {
        $solicitudes = $this->filtradas($request)
            ->orderByRaw("CASE prioridad WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $torres = Torre::orderBy('nombre')->get();
        $vencidas = Solicitud::vencidas()->count();

        return view('mantenimiento.index', compact('solicitudes', 'torres', 'vencidas'));
    }

    public function exportar(Request $request): Response
    {
        $solicitudes = $this->filtradas($request)->latest()->get();

        $filas = [['ID', 'Prioridad', 'Estado', 'Lugar', 'Activo', 'Tipo', 'Descripción', 'Reportado por', 'Técnico', 'Reportado', 'Cerrado']];

        foreach ($solicitudes as $s) {
            $filas[] = [
                $s->id,
                $s->prioridad,
                $s->estado,
                $s->lugar(),
                $s->equipo->nombre ?? '',
                Etiquetas::TIPOS_SOLICITUD[$s->tipo] ?? $s->tipo,
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
            'Content-Disposition' => 'attachment; filename="mantenimiento-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    private function filtradas(Request $request): Builder
    {
        $torreId = $request->integer('torre_id');

        return Solicitud::query()
            ->with(['ubicacion.area.piso.torre', 'habitacion.piso.torre', 'equipo', 'tecnico'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('prioridad'), fn ($q) => $q->where('prioridad', $request->string('prioridad')))
            ->when($request->filled('lugar'), fn ($q) => $request->input('lugar') === 'habitacion'
                ? $q->whereNotNull('habitacion_id')
                : $q->whereNull('habitacion_id'))
            ->when($torreId, fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('habitacion.piso', fn ($q) => $q->where('torre_id', $torreId))
                ->orWhereHas('ubicacion.area.piso', fn ($q) => $q->where('torre_id', $torreId))))
            ->when($request->boolean('vencidas'), fn ($q) => $q->vencidas());
    }

    public function show(Solicitud $solicitud): View
    {
        $solicitud->load(['ubicacion.area.piso.torre', 'habitacion.piso.torre', 'equipo', 'tecnico', 'seguimientos.usuario', 'evidencias']);

        return view('mantenimiento.show', compact('solicitud'));
    }

    public function tomar(Solicitud $solicitud, Request $request): RedirectResponse
    {
        $tomada = DB::transaction(function () use ($solicitud) {
            $bloqueada = Solicitud::whereKey($solicitud->id)->lockForUpdate()->first();

            if ($bloqueada->estado !== 'pendiente') {
                return false;
            }

            $bloqueada->update([
                'estado' => 'en_proceso',
                'tecnico_id' => Auth::id(),
                'fecha_asignacion' => now(),
            ]);

            Auditoria::registrar('tomar_solicitud', 'solicitudes', $bloqueada->id);

            return true;
        });

        if (! $tomada) {
            return back()->withErrors(['estado' => 'Este reporte ya fue tomado.']);
        }

        return back()->with('status', 'Reporte asignado a ti.');
    }

    public function comentar(Solicitud $solicitud, ComentarSolicitudRequest $request): RedirectResponse
    {
        $solicitud->seguimientos()->create([
            'usuario_id' => Auth::id(),
            'comentario' => $request->validated('comentario'),
        ]);

        return back()->with('status', 'Comentario agregado.');
    }

    public function cerrar(Solicitud $solicitud, CerrarSolicitudRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $cerrada = DB::transaction(function () use ($solicitud, $validated) {
            $bloqueada = Solicitud::whereKey($solicitud->id)->lockForUpdate()->first();

            if (! in_array($bloqueada->estado, ['pendiente', 'en_proceso'], true)) {
                return false;
            }

            $bloqueada->update([
                'estado' => $validated['estado'],
                'tecnico_id' => $bloqueada->tecnico_id ?? Auth::id(),
                'fecha_cierre' => now(),
            ]);

            if (! empty($validated['comentario'])) {
                $bloqueada->seguimientos()->create([
                    'usuario_id' => Auth::id(),
                    'comentario' => $validated['comentario'],
                ]);
            }

            Auditoria::registrar('cerrar_solicitud', 'solicitudes', $bloqueada->id, $validated['estado']);

            return true;
        });

        if (! $cerrada) {
            return back()->withErrors(['estado' => 'Este reporte ya está cerrado.']);
        }

        return redirect()->route('panel.mantenimiento.show', $solicitud)->with('status', 'Reporte cerrado.');
    }
}
