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
    // FONCTION DE TEST À SUPPRIMER APRÈS
    // public function getReservationsByCollaborateur(string $collaborateurId)
    // {
    //     return Reservation::with(['collaborateur', 'place.departement'])
    //         ->where('collaborateur_id', $collaborateurId)
    //         ->whereNull('deleted_at')
    //         ->orderBy('date_reservation', 'asc')
    //         ->get()
    //         ->map(function ($res) {
    //             return [
    //                 'id' => $res->id,
    //                 'date_reservation' => $res->date_reservation->format('d-m-Y'),
    //                 'place_label' => $res->place->name ?? '',
    //                 'departement_label' => $res->place->departement->label ?? '',
    //             ];
    //         });
    // }

    public function getReservationsByCollaborateur(string $collaborateurId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->addMonth()->endOfMonth();
        return Reservation::with(['collaborateur', 'place.departement'])->where('collaborateur_id', $collaborateurId)->where('deleted_at', null)->whereBetween('date_reservation', [$startDate, $endDate])->orderBy('date_reservation', 'asc')->get()->map(function ($res) {
            return ['id' => $res->id, 'date_reservation' => $res->date_reservation->format('d-m-Y'), 'place_label' => $res->place->name ?? '', 'departement_label' => $res->place->departement->label ?? '',];
        });
    }
}
