<?php

namespace App\Http\Controllers;

use App\Http\Requests\EvidenciaRequest;
use App\Models\Auditoria;
use App\Models\Evidencia;
use App\Models\Solicitud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EvidenciaController extends Controller
{
    public function store(Solicitud $solicitud, EvidenciaRequest $request): RedirectResponse
    {
        $evidencia = $solicitud->evidencias()->create([
            'ruta_archivo' => $request->file('foto')->store('evidencias', 'public'),
            'subido_por' => Auth::id(),
        ]);

        Auditoria::registrar('subir_evidencia', 'evidencias', $evidencia->id, "Solicitud #{$solicitud->id}");

        return back()->with('status', 'Foto agregada.');
    }

    public function destroy(Evidencia $evidencia): RedirectResponse
    {
        $solicitud = $evidencia->solicitud;

        Storage::disk('public')->delete($evidencia->ruta_archivo);
        Auditoria::registrar('eliminar_evidencia', 'evidencias', $evidencia->id, "Solicitud #{$solicitud->id}");
        $evidencia->delete();

        return redirect()->route('panel.show', $solicitud)->with('status', 'Foto eliminada.');
    }
}
