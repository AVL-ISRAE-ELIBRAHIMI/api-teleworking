<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ListUserReservationService
{

    /**
     * Lister les réservations liées à un collaborateur.
     *
     * @param string $collaborateurId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReservationsByCollaborateur(string $collaborateurId)
    {
        return Reservation::with(['collaborateur', 'place.departement'])
            ->where('collaborateur_id', $collaborateurId)
            ->whereMonth('date_reservation', Carbon::now()->month)
            ->whereYear('date_reservation', Carbon::now()->year)
            ->get()
            ->map(function ($res) {
                return [
                    'id' => $res->id,
                    'date_reservation' => $res->date_reservation->format('d-m-Y'),
                    'status' => $res->status,
                    'place_label' => $res->place->label ?? '',
                    'departement_label' => $res->place->departement->label ?? '',
                ];
            });
    }
    
}
