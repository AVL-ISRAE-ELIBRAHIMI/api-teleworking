<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DepartementService
{
    /**
     * Statistiques globales des réservations (RH / ADMIN)
     */
    //par deparetement
    // public function reservationsStats()
    // {
    //     $currentMonth = Carbon::now();
    //     $nextMonth = Carbon::now()->addMonth();

    //     // Totaux fixes par département (places disponibles)
    //     $totals = [
    //         'ESW' => 56,
    //         'VSP' => 56,
    //         'MDS' => 40,
    //     ];

    //     // 🔹 Définir les bornes de la période (1er jour du mois courant → dernier jour du mois prochain)
    //     $startDate = $currentMonth->copy()->startOfMonth();
    //     $endDate = $nextMonth->copy()->endOfMonth();

    //     // 🔹 Récupération des réservations groupées par jour et département
    //     $stats = DB::table('departements')
    //         ->leftJoin('places', 'departements.id', '=', 'places.departement_id')
    //         ->leftJoin('reservations', function ($join) use ($startDate, $endDate) {
    //             $join->on('places.id', '=', 'reservations.place_id')
    //                 ->whereBetween('reservations.date_reservation', [$startDate, $endDate]);
    //         })
    //         ->whereIn('departements.id', [1, 2, 3])
    //         ->selectRaw("
    //         departements.label as departement_name,
    //         DATE(reservations.date_reservation) as day,
    //         COUNT(reservations.id) as total_reservations
    //     ")
    //         ->groupBy('departements.label', 'day')
    //         ->orderBy('day')
    //         ->get();

    //     // 🔹 Générer la liste complète des jours entre les deux mois
    //     $days = [];
    //     $period = CarbonPeriod::create($startDate, $endDate);
    //     foreach ($period as $date) {
    //         $days[] = $date->format('Y-m-d');
    //     }

    //     // 🔹 Structure de sortie
    //     $result = [
    //         'days' => $days,
    //         'departments' => [],
    //     ];

    //     // Initialisation à 0% pour chaque jour
    //     foreach ($totals as $depName => $totalPlaces) {
    //         foreach ($days as $day) {
    //             $result['departments'][$depName][$day] = 0;
    //         }
    //     }

    //     // 🔹 Calcul du pourcentage par jour et département
    //     foreach ($stats as $row) {
    //         $depName = $row->departement_name;
    //         $totalPlaces = $totals[$depName] ?? 0;
    //         $percent = $totalPlaces > 0 ? round(($row->total_reservations / $totalPlaces) * 100, 2) : 0;

    //         if (isset($result['departments'][$depName][$row->day])) {
    //             $result['departments'][$depName][$row->day] = $percent;
    //         }
    //     }

    //     return $result;
    // }

    //total
    public function reservationsStats()
    {
        $currentMonth = Carbon::now();
        $nextMonth = Carbon::now()->addMonth();

        // 🔹 Totaux fixes par département (places disponibles)
        $totals = [
            'ESW' => 56,
            'VSP' => 56,
            'MDS' => 40,
        ];

        // 🔹 Total global de places
        $totalPlacesGlobal = array_sum($totals); // = 152

        // 🔹 Définir la période (mois courant → mois prochain)
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $nextMonth->copy()->endOfMonth();

        // 🔹 Récupération des réservations groupées par jour (tous départements confondus)
        $stats = DB::table('reservations')
            ->join('places', 'reservations.place_id', '=', 'places.id')
            ->join('departements', 'places.departement_id', '=', 'departements.id')
            ->whereIn('departements.id', [1, 2, 3])
            ->whereBetween('reservations.date_reservation', [$startDate, $endDate])
            ->selectRaw("
            DATE(reservations.date_reservation) as day,
            COUNT(reservations.id) as total_reservations
        ")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // 🔹 Générer tous les jours de la période
        $days = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $days[] = $date->format('Y-m-d');
        }

        // 🔹 Structure du résultat
        $result = [
            'days' => $days,
            'percentages' => [],
        ];

        // Initialisation à 0% pour chaque jour
        foreach ($days as $day) {
            $result['percentages'][$day] = 0;
        }

        // 🔹 Calcul du pourcentage global par jour
        foreach ($stats as $row) {
            $percent = $totalPlacesGlobal > 0
                ? round(($row->total_reservations / $totalPlacesGlobal) * 100, 2)
                : 0;

            if (isset($result['percentages'][$row->day])) {
                $result['percentages'][$row->day] = $percent;
            }
        }

        return $result;
    }



    /**
     * Statistiques journalières pour un utilisateur STL (uniquement son département)
     */

    public function reservationsStatsSTL()
    {
        $user = Auth::user();

        if (!$user->hasRole('STL')) {
            throw new \Exception('Accès non autorisé');
        }

        $departementId = $user->departement_id;
        $depName = DB::table('departements')->where('id', $departementId)->value('label');

        // 🔹 Définir la période : du début du mois courant à la fin du mois prochain
        $currentMonth = Carbon::now();
        $nextMonth = Carbon::now()->addMonth();
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $nextMonth->copy()->endOfMonth();

        // 🔹 Totaux fixes (places disponibles)
        $totals = [
            'ESW' => 56,
            'VSP' => 56,
            'MDS' => 40,
        ];

        // 🔹 Récupération des réservations par jour pour ce département
        $stats = DB::table('departements')
            ->leftJoin('places', 'departements.id', '=', 'places.departement_id')
            ->leftJoin('reservations', function ($join) use ($startDate, $endDate) {
                $join->on('places.id', '=', 'reservations.place_id')
                    ->whereBetween('reservations.date_reservation', [$startDate, $endDate]);
            })
            ->where('departements.id', $departementId)
            ->selectRaw("
            DATE(reservations.date_reservation) as day,
            COUNT(reservations.id) as total_reservations
        ")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // 🔹 Générer tous les jours de la période
        $days = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $days[] = $date->format('Y-m-d');
        }

        // 🔹 Structure du résultat
        $result = [
            'department' => $depName,
            'days' => $days,
            'percentages' => [],
        ];

        // Initialisation à 0%
        foreach ($days as $day) {
            $result['percentages'][$day] = 0;
        }

        // 🔹 Calcul du pourcentage par jour
        $totalPlaces = $totals[$depName] ?? 0;
        foreach ($stats as $row) {
            if ($row->day && $totalPlaces > 0) {
                $percent = round(($row->total_reservations / $totalPlaces) * 100, 2);
                $result['percentages'][$row->day] = $percent;
            }
        }

        return $result;
    }
}
