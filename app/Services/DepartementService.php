<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartementService
{
    /**
     * Statistiques globales des réservations (RH / ADMIN)
     */
    public function reservationsStats()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        // Totaux fixes par département
        $totals = [
            'ESW' => 56,
            'VSP' => 56,
            'MDS' => 40,
        ];

        // 🔹 Récupération des réservations entre les 2 mois
        $stats = DB::table('departements')
            ->leftJoin('places', 'departements.id', '=', 'places.departement_id')
            ->leftJoin('reservations', function ($join) use ($prevMonth, $currentMonth) {
                $join->on('places.id', '=', 'reservations.place_id')
                    ->whereBetween('reservations.date_reservation', [
                        Carbon::parse($prevMonth . '-01')->startOfMonth(),
                        Carbon::parse($currentMonth . '-01')->endOfMonth(),
                    ]);
            })
            ->whereIn('departements.id', [1, 2, 3])
            ->selectRaw("
                departements.label as departement_name,
                DATE_FORMAT(reservations.date_reservation, '%Y-%m') as month,
                COUNT(reservations.id) as total_reservations
            ")
            ->groupBy('departements.label', 'month')
            ->get();

        // 🔹 Structure du résultat
        $result = [
            'months' => [$prevMonth, $currentMonth],
            'departments' => []
        ];

        // Initialisation à 0%
        foreach ($totals as $depName => $totalPlaces) {
            $result['departments'][$depName] = [
                $prevMonth => 0,
                $currentMonth => 0
            ];
        }

        // Calcul du pourcentage par département et mois
        foreach ($stats as $row) {
            $depName = $row->departement_name;
            $totalPlaces = $totals[$depName] ?? 0;
            $percent = $totalPlaces > 0 ? round(($row->total_reservations / $totalPlaces) * 100, 2) : 0;

            $result['departments'][$depName][$row->month] = $percent;
        }

        return $result;
    }

    /**
     * Statistiques pour un utilisateur STL (uniquement son département)
     */
    public function reservationsStatsSTL()
    {
        $user = Auth::user();

        if (!$user->hasRole('STL')) {
            throw new \Exception('Accès non autorisé');
        }

        $departementId = $user->departement_id;
        $currentMonth = Carbon::now()->format('Y-m');
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        $totals = [
            'ESW' => 56,
            'VSP' => 56,
            'MDS' => 40,
        ];

        $stats = DB::table('departements')
            ->leftJoin('places', 'departements.id', '=', 'places.departement_id')
            ->leftJoin('reservations', function ($join) use ($prevMonth, $currentMonth) {
                $join->on('places.id', '=', 'reservations.place_id')
                    ->whereBetween('reservations.date_reservation', [
                        Carbon::parse($prevMonth . '-01')->startOfMonth(),
                        Carbon::parse($currentMonth . '-01')->endOfMonth(),
                    ]);
            })
            ->where('departements.id', $departementId)
            ->selectRaw("
                departements.label as departement_name,
                DATE_FORMAT(reservations.date_reservation, '%Y-%m') as month,
                COUNT(reservations.id) as total_reservations
            ")
            ->groupBy('departements.label', 'month')
            ->get();

        $depName = DB::table('departements')->where('id', $departementId)->value('label');

        $result = [
            'months' => [$prevMonth, $currentMonth],
            'department' => $depName,
            'percentages' => [
                $prevMonth => 0,
                $currentMonth => 0
            ]
        ];

        foreach ($stats as $row) {
            $totalPlaces = $totals[$row->departement_name] ?? 0;
            $percent = $totalPlaces > 0 ? round(($row->total_reservations / $totalPlaces) * 100, 2) : 0;
            $result['percentages'][$row->month] = $percent;
        }

        return $result;
    }
}
