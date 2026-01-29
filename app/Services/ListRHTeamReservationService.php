<?php

namespace App\Services;

use App\Models\Collaborateur;
use App\Models\Reservation;
use Carbon\Carbon;


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
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->addMonth()->endOfMonth();

        return Reservation::with([
            'collaborateur',
            'collaborateur.equipe',
            'place.departement',
            'overrideReservations' // 🔥 IMPORTANT pour charger les overrides
        ])
            ->whereBetween('date_reservation', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->orderBy('date_reservation', 'asc')
            ->get()
            ->map(function ($res) {
                return [
                    'id' => $res->id,
                    'date_reservation'  => optional($res->date_reservation)->format('d-m-Y'),
                    'place_label'       => $res->place->name ?? '',
                    'departement_label' => $res->place->departement->label ?? '',
                    'equipe_label'      => $res->collaborateur->equipe->label ?? 'Équipe non définie',
                    'collaborateur'     => trim(($res->collaborateur->nom ?? '') . ' ' . ($res->collaborateur->prenom ?? '')),
                    'quota'             => $res->collaborateur->quota ?? '',

                    // 🔥 Le champ override correctement calculé
                    'is_overridden'     => $res->overrideReservations->isNotEmpty(),
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
