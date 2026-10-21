<?php

namespace App\Http\Requests;

use App\Models\Piso;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero' => ['required', 'integer', 'between:-1,8'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((int) $this->numero === -1 && Piso::where('numero', -1)->exists()) {
                $validator->errors()->add('numero', 'Ya existe un sótano, compartido por las tres torres.');
            }
        });
    }
}
