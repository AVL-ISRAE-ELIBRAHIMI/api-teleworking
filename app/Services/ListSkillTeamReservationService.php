<?php

namespace App\Services;

use App\Models\Collaborateur;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ListSkillTeamReservationService
{

    /**
     * Lister les réservations liées à un responsable d'équipe.
     *
     * @param string $teamLeaderId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    // public function getReservationsBySkillTeamLeader(string $skillTeamLeaderId)
    // {
    //     // 1. Récupérer le département et l'équipe du team leader
    //     $teamLeader = Collaborateur::with(['departement', 'equipe'])->findOrFail($skillTeamLeaderId);
    //     $departementId = $teamLeader->departement_id;
    //     $departementLabel = $teamLeader->departement->label ?? 'Département inconnu';
    //     $equipeLabel = $teamLeader->equipe->label ?? 'Équipe inconnue';

    //     // 2. Récupérer tous les collaborateurs de ce département
    //     $collaborateursIds = Collaborateur::where('departement_id', $departementId)
    //         ->pluck('id')
    //         ->map(function ($id) {
    //             return (string)$id;
    //         })
    //         ->toArray();

    //     // 3. Date range: current + next month
    //     $startDate = Carbon::now()->startOfMonth();
    //     $endDate = Carbon::now()->addMonth()->endOfMonth();

    //     // 4. Récupérer les réservations avec les relations nécessaires
    //     return Reservation::with([
    //         'collaborateur',
    //         'place.departement',
    //         'collaborateur.equipe'
    //     ])
    //         ->whereIn('collaborateur_id', $collaborateursIds)
    //         ->whereBetween('date_reservation', [$startDate, $endDate])
    //         ->orderBy('date_reservation', 'asc')
    //         ->get()
    //         ->map(function ($res) use ($departementLabel, $equipeLabel) {
    //             return [
    //                 'id' => $res->id,
    //                 'date_reservation' => $res->date_reservation->format('d-m-Y'),
    //                 'place_label' => $res->place->name ?? '',
    //                 'departement_label' => $res->place->departement->label ?? '',
    //                 'equipe_label' => $res->collaborateur->equipe->label ?? 'Équipe non définie',
    //                 'collaborateur' => trim(($res->collaborateur->nom ?? '') . ' ' . ($res->collaborateur->prenom ?? '')),
    //                 'quota' => ($res->collaborateur->quota ?? ''),
    //             ];
    //         });
    // }

    public function getReservationsBySkillTeamLeader(string $skillTeamLeaderId)
    {
        $teamLeader = Collaborateur::with(['departement', 'equipe'])
            ->findOrFail($skillTeamLeaderId);

        $departementId = $teamLeader->departement_id;

        // Tous les collaborateurs du même département
        $collaborateursIds = Collaborateur::where('departement_id', $departementId)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        // Les réservations avec override LEFT JOIN
        return Reservation::with(['collaborateur', 'place.departement', 'collaborateur.equipe'])
            ->leftJoin('override_reservations', 'override_reservations.reservation_id', '=', 'reservations.id')
            ->whereIn('collaborateur_id', $collaborateursIds)
            ->orderBy('date_reservation', 'asc')
            ->get([
                'reservations.*',
                DB::raw('CASE WHEN override_reservations.id IS NULL THEN 0 ELSE 1 END as is_overridden')
            ])
            ->map(function ($res) {
                return [
                    'id' => $res->id,
                    'collaborateur_id' => $res->collaborateur_id,
                    'date_reservation' => Carbon::parse($res->date_reservation)->format('d-m-Y'),
                    'place_label' => $res->place->name ?? '',
                    'departement_label' => $res->place->departement->label ?? '',
                    'equipe_label' => $res->collaborateur->equipe->label ?? '',
                    'collaborateur' => trim(($res->collaborateur->nom ?? '') . ' ' . ($res->collaborateur->prenom ?? '')),
                    'is_overridden' => (bool)$res->is_overridden,
                ];
            });
    }


    public function getDepartementUsers(string $collaborateurId)
    {
        // 🔹 Récupérer le collaborateur pour connaître son département
        $collaborateur = Collaborateur::findOrFail($collaborateurId);

        // 🔹 Récupérer tous les collaborateurs du même département
        return Collaborateur::with(['departement', 'equipe'])
            ->where('departement_id', $collaborateur->departement_id)
            ->get()
            ->map(function ($collab) {
                return [
                    'id' => $collab->id,
                    'collaborateur' => trim(($collab->nom ?? '') . ' ' . ($collab->prenom ?? '')),
                    'departement_label' => $collab->departement->label ?? '',
                    'equipe_label' => $collab->equipe->label ?? 'Équipe non définie',
                    'quota' => $collab->quota ?? '',
                ];
            });
    }
}
