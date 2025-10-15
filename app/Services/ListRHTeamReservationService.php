<?php

namespace App\Services;

use App\Models\Collaborateur;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ListRHTeamReservationService
{

    /**
     * Lister les réservations liées à un responsable d'équipe.
     *
     * @param string $teamLeaderId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReservationsByRH(string $rhId)
    {
       // Pourquoi: on ignore volontairement l'RH et ses contraintes comme demandé
        return Reservation::with([
                'collaborateur',
                'collaborateur.equipe',
                'place.departement',
            ])
            ->whereMonth('date_reservation', Carbon::now()->month)
            ->whereYear('date_reservation', Carbon::now()->year)
            ->get()
            ->map(function ($res) {
                return [
                    'id' => $res->id,
                    'date_reservation'   => optional($res->date_reservation)->format('d-m-Y'),
                    'place_label'        => $res->place->name ?? '',
                    'departement_label'  => $res->place->departement->label ?? '',
                    'equipe_label'       => $res->collaborateur->equipe->label ?? 'Équipe non définie',
                    'collaborateur'      => trim(($res->collaborateur->nom ?? '') . ' ' . ($res->collaborateur->prenom ?? '')),
                    'quota'              => $res->collaborateur->quota ?? '',
                ];
            });
    
    }

    public function getAllUsers()
    {
       
           return Collaborateur::with(['departement', 'equipe'])
        ->get()
        ->map(function ($collab) {
            return [
                'id' => $collab->id,
                'collaborateur' => $collab->nom . ' ' . $collab->prenom,
                'departement_label' => $collab->departement->label ?? '',
                'equipe_label' => $collab->equipe->label ?? 'Équipe non définie',
                'quota' => $collab->quota ?? '',
            ];
        });
    
    }
   
}
