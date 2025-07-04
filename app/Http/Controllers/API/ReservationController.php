<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use App\Models\Place;
use App\Services\ListUserReservationService;
use Illuminate\Http\Request;
use App\Services\ReservationService;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $listReservationService;
    protected $reservationService;

    public function __construct(ListUserReservationService $listReservationService, ReservationService $reservationService)
    {
        $this->listReservationService = $listReservationService;
        $this->reservationService = $reservationService;
    }


    public function index()
    {

        $collaborateurId = session('user.id');

        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }

        $reservations = $this->listReservationService->getReservationsByCollaborateur($collaborateurId);

        return response()->json($reservations);
    }



    // 2. Créer des réservations (déjà existante)
    public function store(Request $request)
    {
        dd($request->all());
        $validated = $request->validate([
            'place_id' => 'required|integer',
            'dates' => 'required|array|min:1',
            'dates.*' => 'date|after_or_equal:today'
        ]);
        $reservations = $this->reservationService->createReservations($validated);

        return response()->json([
            'message' => 'Réservations créées avec succès',
            'data' => $reservations
        ], 201);
    }
    // 4. Vérifier la disponibilité (déjà existante)
    public function getMonthlyAvailability($year, $month)
    {
        $collaborateurId = session('user.id');
        $collaborateur = Collaborateur::findOrFail($collaborateurId);

        $availability = $this->reservationService->getMonthlyAvailability(
            $year,
            $month,
            $collaborateur->departement_id
        );

        return response()->json($availability);
    }

    // 5. Obtenir le layout du bureau (nouvelle méthode)
    public function getOfficeLayout()
    {
        $collaborateurId = session('user.id');
        $collaborateur = Collaborateur::findOrFail($collaborateurId);
        return response()->json([
            'departement_id' => $collaborateur->departement_id,
            'office_name' => $this->getOfficeName($collaborateur->departement_id)
        ]);
    }

    // 6. Méthode helper privée
    private function getOfficeName($departementId)
    {
        return match ($departementId) {
            1 => 'P2 - Merrakech',
            2 => 'P1 - Casablanca',
            3 => 'Standard',
            default => 'Bureau Inconnu'
        };
    }
    public function getPlaces()
    {
        $collaborateurId = session('user.id');

        try {
            // Récupérer le département_id du collaborateur connecté
            $collaborateur = Collaborateur::findOrFail($collaborateurId);

            $places = Place::where('departement_id', $collaborateur->departement_id)
                ->where('is_active', true)
                ->orderBy('name') // Tri par label (A1, A2, A3, etc.)
                ->get();
            return response()->json([
                'places' => $places->map(function ($place) {
                    return [
                        'id' => $place->id,
                        'name' => $place->name, // Retourne A1, A2, etc.
                        'zone' => $place->zone,  // Retourne 'Left Area' ou 'Right Area'
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch places',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getSeatBookingType()
    {
        // Récupération depuis la session Windows
        $sessionUser = session('user');


        // Trouver le collaborateur dans la base de données
        $collaborateur = Collaborateur::where('id', $sessionUser['id'])->first();

        if (!$collaborateur) {
            return response()->json(['error' => 'Collaborateur non trouvé'], 404);
        }

        // Mapping des départements aux composants
        $componentMap = [
            1 => 'SeatBookingP2',
            2 => 'SeatBookingP1',
            3 => 'SeatBooking'
        ];
        return response()->json([
            'departement_id' => $collaborateur->departement_id,
            'component_name' => $componentMap[$collaborateur->departement_id] ?? 'SeatBooking'
        ]);
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
