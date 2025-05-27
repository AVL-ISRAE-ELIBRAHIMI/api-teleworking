<?php

namespace App\Services;

use App\Models\Collaborateur;
use Illuminate\Support\Facades\Session;

class ProfilService
{
    /**
     * Récupère les informations du profil de l'utilisateur connecté.
     *  * @param string $collaborateurId

     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserProfile(string $collaborateurId)
    {
        $collaborateur = Collaborateur::with(['departement', 'equipe'])
            ->where('id', $collaborateurId)
            ->first();
        if (!$collaborateur) {
            return [];
        }
        $mymanager = $collaborateur->manager;
        $manager = Collaborateur::where('id', $mymanager)->first();
        return [
            'nom' => $collaborateur->nom,
            'prenom' => $collaborateur->prenom,
            'email' => $collaborateur->email,
            'departement' => $collaborateur->departement->label ?? '',
            'equipe' => $collaborateur->equipe->label ?? '',
            'manager_nom' => $manager->nom ?? '',
            'manager_prenom' => $manager->prenom ?? '',
            'activity' => $collaborateur->activity,
        ];
    }
}
