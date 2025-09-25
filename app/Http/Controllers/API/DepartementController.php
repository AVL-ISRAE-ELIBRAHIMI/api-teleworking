<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function reservationsStats()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }

        // Mois courant et mois précédent
        $currentMonth = Carbon::now()->format('Y-m');
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

          // ✅ Récupérer les stats par département avec LEFT JOIN
    $stats = DB::table('departements')
        ->leftJoin('places', 'departements.id', '=', 'places.departement_id')
        ->leftJoin('reservations', function ($join) use ($userId, $prevMonth, $currentMonth) {
            $join->on('places.id', '=', 'reservations.place_id')
                 ->where('reservations.collaborateur_id', $userId)
                 ->whereBetween('reservations.date_reservation', [
                     Carbon::parse($prevMonth . '-01')->startOfMonth(),
                     Carbon::parse($currentMonth . '-01')->endOfMonth()
                 ]);
        })
        ->whereIn('departements.id', [1, 2, 3]) // ✅ uniquement départements 1,2,3
        ->selectRaw("
            departements.id as departement_id,
            departements.label as departement_name,
            DATE_FORMAT(reservations.date_reservation, '%Y-%m') as month,
            COALESCE(COUNT(reservations.id), 0) as total
        ")
        ->groupBy('departements.id', 'departements.label', 'month')
        ->get();

        // Préparer résultat
        $result = [
            'months' => [$prevMonth, $currentMonth],
            'departments' => []
        ];

        foreach ([1, 2, 3] as $depId) {
            $depName = $stats->firstWhere('departement_id', $depId)->departement_name ?? "Département $depId";
            $result['departments'][$depName] = [$prevMonth => 0, $currentMonth => 0];
        }

        foreach ($stats as $row) {
            $result['departments'][$row->departement_name][$row->month] = $row->total;
        }

        return response()->json($result);
    }
    public function reservationsStatsSTL()
    {
        $user = Auth::user();

        if (!$user->hasRole('STL')) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $departementId = $user->departement_id;
        $currentMonth = Carbon::now()->format('Y-m');
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        // Réservations des collaborateurs du même département
        $stats = DB::table('reservations')
            ->join('places', 'reservations.place_id', '=', 'places.id')
            ->join('departements', 'places.departement_id', '=', 'departements.id')
            ->selectRaw("
            DATE_FORMAT(reservations.date_reservation, '%Y-%m') as month,
            COUNT(*) as total
        ")
            ->whereIn('reservations.collaborateur_id', function ($query) use ($departementId) {
                $query->select('id')
                    ->from('collaborateurs')
                    ->where('departement_id', $departementId);
            })
            ->whereBetween('reservations.date_reservation', [
                Carbon::parse($prevMonth . '-01')->startOfMonth(),
                Carbon::parse($currentMonth . '-01')->endOfMonth()
            ])
            ->groupBy('month')
            ->pluck('total', 'month');

        // Totaux globaux tous départements 1,2,3
        $totals = DB::table('reservations')
            ->join('places', 'reservations.place_id', '=', 'places.id')
            ->join('departements', 'places.departement_id', '=', 'departements.id')
            ->selectRaw("
            DATE_FORMAT(reservations.date_reservation, '%Y-%m') as month,
            COUNT(*) as total
        ")
            ->whereIn('departements.id', [1, 2, 3])
            ->whereBetween('reservations.date_reservation', [
                Carbon::parse($prevMonth . '-01')->startOfMonth(),
                Carbon::parse($currentMonth . '-01')->endOfMonth()
            ])
            ->groupBy('month')
            ->pluck('total', 'month');

        $depName = DB::table('departements')->where('id', $departementId)->value('label') ?? "Département $departementId";

        return response()->json([
            'months' => [$prevMonth, $currentMonth],
            'department' => $depName,
            'stats' => $stats,
            'totals' => $totals
        ]);
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
