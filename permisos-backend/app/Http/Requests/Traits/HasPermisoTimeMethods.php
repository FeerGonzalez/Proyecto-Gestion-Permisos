<?php

namespace App\Http\Requests\Traits;

use Carbon\Carbon;

trait HasPermisoTimeMethods
{
    /**
     * Get the start time as a Carbon instance.
     */
    public function getInicio(): Carbon
    {
        return Carbon::createFromFormat('H:i', $this->hora_inicio);
    }

    /**
     * Get the end time as a Carbon instance.
     */
    public function getFin(): Carbon
    {
        return Carbon::createFromFormat('H:i', $this->hora_fin);
    }

    /**
     * Calculate total hours between start and end time.
     */
    public function horasTotales(): float
    {
        return $this->getInicio()->floatDiffInHours($this->getFin());
    }
}
