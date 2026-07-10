<?php

namespace App\Services\TAM;

use App\Models\TAM\Materiel;

class MaterielService
{
    /**
     * Récupérer les matériels disponibles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableMateriels()
    {
        return Materiel::all();
    }
}
