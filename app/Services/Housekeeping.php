<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Habitacion;
use App\Models\TareaAseo;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Tareas de aseo: quién limpia qué habitación y cuándo; al terminar, la habitación queda lista. */
class Housekeeping
{
    public function iniciar(TareaAseo $tarea, int $usuarioId): void
    {
        DB::transaction(function () use ($tarea, $usuarioId) {
            $tarea = TareaAseo::whereKey($tarea->id)->lockForUpdate()->firstOrFail();

            if ($tarea->estado !== 'pendiente') {
                throw new DomainException('Esta tarea ya fue iniciada.');
            }

            if ($tarea->asignada_a && $tarea->asignada_a !== $usuarioId) {
                throw new DomainException('Esta tarea está asignada a otra persona.');
            }

            $tarea->update(['estado' => 'en_proceso', 'asignada_a' => $usuarioId, 'inicio_at' => now()]);

            // Una habitación libre pasa a "en limpieza"; si hay huésped (repaso diario), sigue ocupada.
            $habitacion = $tarea->habitacion;
            if (in_array($habitacion->estado, ['sucia', 'disponible'], true)) {
                $habitacion->update(['estado' => 'en_limpieza']);
            }

            Auditoria::registrar('iniciar_aseo', 'tareas_aseo', $tarea->id, "Hab. {$habitacion->numero}");
        });
    }

    public function terminar(TareaAseo $tarea, int $usuarioId, ?string $observaciones = null): void
    {
        DB::transaction(function () use ($tarea, $usuarioId, $observaciones) {
            $tarea = TareaAseo::whereKey($tarea->id)->lockForUpdate()->firstOrFail();

            if ($tarea->estado !== 'en_proceso') {
                throw new DomainException('Solo se puede terminar una tarea en proceso.');
            }

            if ($tarea->asignada_a !== $usuarioId) {
                throw new DomainException('Solo quien inició la limpieza puede terminarla.');
            }

            $tarea->update(['estado' => 'terminada', 'fin_at' => now(), 'observaciones' => $observaciones]);

            $habitacion = Habitacion::with('reservaActual')->find($tarea->habitacion_id);
            if (in_array($habitacion->estado, ['sucia', 'en_limpieza'], true)) {
                $habitacion->update(['estado' => $habitacion->reservaActual ? 'ocupada' : 'disponible']);
            }

            Auditoria::registrar('terminar_aseo', 'tareas_aseo', $tarea->id, "Hab. {$habitacion->numero}");
        });
    }
}
