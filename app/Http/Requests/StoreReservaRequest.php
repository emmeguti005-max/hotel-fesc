<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, Rule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
        ];
    }
}
