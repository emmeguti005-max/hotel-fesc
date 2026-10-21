<?php

namespace App\Http\Requests;

use App\Support\Etiquetas;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Un activo está en una habitación o en una zona común: exactamente uno de los dos.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'habitacion_id' => ['nullable', 'required_without:ubicacion_id', 'prohibits:ubicacion_id', 'exists:habitaciones,id'],
            'ubicacion_id' => ['nullable', 'required_without:habitacion_id', 'exists:ubicaciones,id'],
            'tipo' => ['required', Rule::in(array_keys(Etiquetas::TIPOS_EQUIPO))],
            'codigo' => ['nullable', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:100'],
            'usuario_asignado' => ['nullable', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'serie' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'in:activo,de_baja'],
            'pos_x' => ['nullable', 'numeric', 'between:0,100'],
            'pos_y' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'habitacion_id.required_without' => 'Indica la habitación o la zona común donde está el activo.',
            'ubicacion_id.required_without' => 'Indica la habitación o la zona común donde está el activo.',
            'habitacion_id.prohibits' => 'Elige una habitación o una zona común, no ambas.',
        ];
    }

    /** El campo que no se usa llega vacío; se guarda como NULL para que el activo quede en un solo lugar. */
    public function validated($key = null, $default = null)
    {
        $datos = parent::validated($key, $default);

        if ($key === null) {
            $datos['habitacion_id'] ??= null;
            $datos['ubicacion_id'] ??= null;
        }

        return $datos;
    }
}
