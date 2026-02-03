<?php

namespace App\Http\Requests\Permisos;

use App\Http\Requests\Traits\HasPermisoTimeMethods;
use Illuminate\Foundation\Http\FormRequest;

class StorePermisoRequest extends FormRequest
{
    use HasPermisoTimeMethods;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'motivo' => 'required|string|max:500',
        ];
    }
}

