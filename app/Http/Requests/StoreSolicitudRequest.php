<?php

namespace App\Http\Requests;

use App\Models\Equipo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * El daño está en una habitación o en una zona común: exactamente uno de los dos.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'habitacion_id' => ['nullable', 'required_without:ubicacion_id', 'prohibits:ubicacion_id', 'exists:habitaciones,id'],
            'ubicacion_id' => ['nullable', 'required_without:habitacion_id', 'exists:ubicaciones,id'],
            'equipo_id' => ['nullable', 'exists:equipos,id'],
            'nombre_solicitante' => ['nullable', 'string', 'max:100'],
            'tipo' => ['required', 'in:electrico,plomeria,aire_acondicionado,tv_entretenimiento,wifi,cerrajeria,mobiliario,equipo_cocina,otro'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'prioridad' => ['required', 'in:baja,media,alta,urgente'],
        ];
    }

    public function messages(): array
    {
        return [
            'habitacion_id.required_without' => 'Indica la habitación o la zona común donde está el daño.',
            'ubicacion_id.required_without' => 'Indica la habitación o la zona común donde está el daño.',
            'habitacion_id.prohibits' => 'Elige una habitación o una zona común, no ambas.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->equipo_id)) {
                return;
            }

            $equipo = Equipo::find($this->equipo_id);
            if (! $equipo) {
                return;
            }

            $coincide = $this->habitacion_id
                ? $equipo->habitacion_id === (int) $this->habitacion_id
                : $equipo->ubicacion_id === (int) $this->ubicacion_id;

            if (! $coincide) {
                $validator->errors()->add('equipo_id', 'El activo seleccionado no está en ese lugar.');
            }
        });
    }
}
