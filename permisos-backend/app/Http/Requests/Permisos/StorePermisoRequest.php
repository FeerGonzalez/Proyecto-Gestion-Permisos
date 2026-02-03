<?php

namespace App\Http\Requests\Permisos;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
class StorePermisoRequest extends FormRequest
{
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
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'motivo' => 'required|string|max:500',
        ];
    }

    public function getInicio(): Carbon
    {
        return Carbon::createFromFormat('H:i', $this->hora_inicio);
    }

    public function getFin(): Carbon
    {
        return Carbon::createFromFormat('H:i', $this->hora_fin);
    }

    public function horasTotales(): float
    {
        return $this->getInicio()->floatDiffInHours($this->getFin());
    }
}
