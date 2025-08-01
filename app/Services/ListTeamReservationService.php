<?php

namespace App\Services;

use App\Models\Collaborateur;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ListTeamReservationService
{

    /**
     * Lister les réservations liées à un responsable d'équipe.
     *
     * @param string $teamLeaderId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReservationsByTeamLeader(string $teamLeaderId)
    {
        // 1. Récupérer l'équipe du team leader
        $teamLeader = Collaborateur::with('equipe')->findOrFail($teamLeaderId);
        $equipeId = $teamLeader->equipe_id;
        $equipeLabel = $teamLeader->equipe->label ?? 'Équipe inconnue';

        // 2. Récupérer tous les collaborateurs de cette équipe (version compatible UUID et ID entier)
        $collaborateursIds = Collaborateur::where('equipe_id', $equipeId)->pluck('id')->toArray();
      
        // 3. Récupérer les réservations avec les relations
        return Reservation::with([
            'collaborateur',
            'place.departement'
        ])
            ->whereIn('collaborateur_id', $collaborateursIds)
            ->whereMonth('date_reservation', Carbon::now()->month)
            ->whereYear('date_reservation', Carbon::now()->year)
            ->get()
            ->map(function ($res) use ($equipeLabel) {
                return [
                    'id' => $res->id,
                    'date_reservation' => $res->date_reservation->format('d-m-Y'),
                    'place_label' => $res->place->name ?? '',
                    'departement_label' => $res->place->departement->label ?? '',
                    'collaborateur' => ($res->collaborateur->nom ?? '') . ' ' . ($res->collaborateur->prenom ?? '')     ,
                    'equipe_label' => $equipeLabel,
                ];
            });
    }
}
