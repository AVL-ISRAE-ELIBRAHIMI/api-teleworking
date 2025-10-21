<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\DepartementService;
use Illuminate\Http\JsonResponse;


class DepartementController extends Controller
{

    // public function reservationsStats()
    // {
    //     $currentMonth = Carbon::now()->format('Y-m');
    //     $prevMonth = Carbon::now()->subMonth()->format('Y-m');

    //     // Totaux fixes
    //     $totals = [
    //         'ESW' => 56,
    //         'VSP' => 56,
    //         'MDS' => 40,
    //     ];

    //     // Récupérer les réservations globales
    //     $stats = DB::table('departements')
    //         ->leftJoin('places', 'departements.id', '=', 'places.departement_id')
    //         ->leftJoin('reservations', function ($join) use ($prevMonth, $currentMonth) {
    //             $join->on('places.id', '=', 'reservations.place_id')
    //                 ->whereBetween('reservations.date_reservation', [
    //                     Carbon::parse($prevMonth . '-01')->startOfMonth(),
    //                     Carbon::parse($currentMonth . '-01')->endOfMonth()
    //                 ]);
    //         })
    //         ->whereIn('departements.id', [1, 2, 3])
    //         ->selectRaw("
    //         departements.label as departement_name,
    //         DATE_FORMAT(reservations.date_reservation, '%Y-%m') as month,
    //         COUNT(reservations.id) as total_reservations
    //     ")
    //         ->groupBy('departements.label', 'month')
    //         ->get();

    //     // Préparer résultat
    //     $result = [
    //         'months' => [$prevMonth, $currentMonth],
    //         'departments' => []
    //     ];

    //     // Initialiser chaque département avec 0%
    //     foreach ($totals as $depName => $totalPlaces) {
    //         $result['departments'][$depName] = [
    //             $prevMonth => 0,
    //             $currentMonth => 0
    //         ];
    //     }

    //     // Remplir avec les bons pourcentages
    //     foreach ($stats as $row) {
    //         $depName = $row->departement_name;
    //         $totalPlaces = $totals[$depName] ?? 0;
    //         $percent = $totalPlaces > 0 ? round(($row->total_reservations / $totalPlaces) * 100, 2) : 0;

    //         $result['departments'][$depName][$row->month] = $percent;
    //     }

    //     return response()->json($result);
    // }

    // public function reservationsStatsSTL()
    // {
    //     $user = Auth::user();

    //     if (!$user->hasRole('STL')) {
    //         return response()->json(['error' => 'Accès non autorisé'], 403);
    //     }

    //     $departementId = $user->departement_id;
    //     $currentMonth = Carbon::now()->format('Y-m');
    //     $prevMonth = Carbon::now()->subMonth()->format('Y-m');

    //     // Totaux fixes
    //     $totals = [
    //         'ESW' => 56,
    //         'VSP' => 56,
    //         'MDS' => 40,
    //     ];

    //     // Réutiliser la requête de la première fonction
    //     $stats = DB::table('departements')
    //         ->leftJoin('places', 'departements.id', '=', 'places.departement_id')
    //         ->leftJoin('reservations', function ($join) use ($prevMonth, $currentMonth) {
    //             $join->on('places.id', '=', 'reservations.place_id')
    //                 ->whereBetween('reservations.date_reservation', [
    //                     Carbon::parse($prevMonth . '-01')->startOfMonth(),
    //                     Carbon::parse($currentMonth . '-01')->endOfMonth()
    //                 ]);
    //         })
    //         ->where('departements.id', $departementId) //  filtre sur le département du STL
    //         ->selectRaw("
    //         departements.label as departement_name,
    //         DATE_FORMAT(reservations.date_reservation, '%Y-%m') as month,
    //         COUNT(reservations.id) as total_reservations
    //     ")
    //         ->groupBy('departements.label', 'month')
    //         ->get();

    //     // Récupérer le nom du département
    //     $depName = DB::table('departements')->where('id', $departementId)->value('label');

    //     // Initialiser résultat
    //     $result = [
    //         'months' => [$prevMonth, $currentMonth],
    //         'department' => $depName,
    //         'percentages' => [
    //             $prevMonth => 0,
    //             $currentMonth => 0
    //         ]
    //     ];

    //     // Calcul du pourcentage comme dans la 1ère fonction
    //     foreach ($stats as $row) {
    //         $totalPlaces = $totals[$row->departement_name] ?? 0;
    //         $percent = $totalPlaces > 0 ? round(($row->total_reservations / $totalPlaces) * 100, 2) : 0;
    //         $result['percentages'][$row->month] = $percent;
    //     }

    //     return response()->json($result);
    // }
    protected $departementService;

    public function __construct(DepartementService $departementService)
    {
        $this->departementService = $departementService;
    }

    public function reservationsStats(): JsonResponse
    {
        try {
            $result = $this->departementService->reservationsStats();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du calcul des statistiques',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reservationsStatsSTL(): JsonResponse
    {
        try {
            $result = $this->departementService->reservationsStatsSTL();
            return response()->json($result);
        } catch (\Exception $e) {
            $status = $e->getMessage() === 'Accès non autorisé' ? 403 : 500;
            return response()->json([
                'error' => $e->getMessage(),
            ], $status);
        }
    }
}
