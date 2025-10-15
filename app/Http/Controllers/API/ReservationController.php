<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use App\Models\Place;
use App\Models\Reservation;
use App\Services\ListRHTeamReservationService;
use App\Services\ListSkillTeamReservationService;
use App\Services\ListTeamReservationService;
use App\Services\ListUserReservationService;
use Illuminate\Http\Request;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $listReservationService;
    protected $listTeamReservationService;
    protected $listSkillTeamReservationService;
    protected $reservationService;
    protected $listRHTeamReservationService;

    public function __construct(ListSkillTeamReservationService $listSkillTeamReservationService, ListUserReservationService $listReservationService, ListTeamReservationService $listTeamReservationService, ListRHTeamReservationService $listRHTeamReservationService, ReservationService $reservationService)
    {
        $this->listSkillTeamReservationService = $listSkillTeamReservationService;
        $this->listReservationService = $listReservationService;
        $this->listTeamReservationService = $listTeamReservationService;
        $this->listRHTeamReservationService = $listRHTeamReservationService;
        $this->reservationService = $reservationService;
    }


    public function index()
    {
        $collaborateurId = Auth::user()->id;

        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }

        $reservations = $this->listReservationService->getReservationsByCollaborateur($collaborateurId);

        $collaborateur = Collaborateur::findOrFail($collaborateurId);
        return response()->json([
            'reservations' => $reservations,
            'currentUserId' => $collaborateurId,
            'quotaUser' => $collaborateur->quota,
        ]);
    }
    public function index_all()
    {
        $collaborateurId = Auth::User()->id;

        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }

        $reservations = $this->listRHTeamReservationService->getReservationsByRH($collaborateurId);

        return response()->json($reservations);
    }

    public function getAllUsers()
    {
        $collaborateurId = Auth::User()->id;

        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }

        $users = $this->listRHTeamReservationService->getAllUsers();

        return response()->json($users);
    }

    public function getDepartementUsers()
    {
        $collaborateurId = Auth::User()->id;

        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }

        $users = $this->listSkillTeamReservationService->getDepartementUsers($collaborateurId);

        return response()->json($users);
    }

    public function index_for_team_leads()
    {

        $collaborateurId = Auth::User()->id;

        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }

        $reservations = $this->listTeamReservationService->getReservationsByTeamLeader($collaborateurId);

        return response()->json($reservations);
    }

    public function index_for_skill_team_leads()
    {

        $collaborateurId = Auth::User()->id;

        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }

        $reservations = $this->listSkillTeamReservationService->getReservationsBySkillTeamLeader($collaborateurId);

        return response()->json($reservations);
    }

    // 2. Créer des réservations (déjà existante)
    public function store(Request $request)
    {
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
    public function getMonthlyAvailability($year, $month, $departement_id = null)
    {
        $collaborateurId = Auth::user()->id;
    
        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }
    
        $collaborateur = Collaborateur::findOrFail($collaborateurId);
    
        if ($collaborateur->departement_id == 4) {
            $deptToQuery = $departement_id ?? 4;
        } else {
            $deptToQuery = $collaborateur->departement_id;
        }
    
        $availability = $this->reservationService->getMonthlyAvailability(
            $year,
            $month,
            $deptToQuery
        );
    
        return response()->json($availability);
    }
   
    // 5. Obtenir le layout du bureau (nouvelle méthode)
    public function getOfficeLayout()
    {
        $collaborateurId = Auth::User()->id;
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
    public function getPlaces($departement_id = null)
    {
        $collaborateurId = Auth::user()->id;
    
        try {
            $collaborateur = Collaborateur::findOrFail($collaborateurId);
    
            if ($collaborateur->departement_id == 4) {
                // HR user
                if ($departement_id !== null) {
                    // Specific department requested
                    $places = Place::where('departement_id', $departement_id)
                        ->orderBy('name')
                        ->get();
                } else {
                    // All places
                    $places = Place::all();
                }
            } else {
                $places = Place::where('departement_id', $collaborateur->departement_id)
                    ->orderBy('name')
                    ->get();
            }
    
            return response()->json([
                'places' => $places->map(function ($place) {
                    return [
                        'id' => $place->id,
                        'name' => $place->name,
                        'zone' => $place->zone,
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
        $collaborateur = Auth::user();

        if (!$collaborateur) {
            return response()->json(['error' => 'No authenticated user'], 401);
        }

        $componentMap = [
            1 => 'SeatBookingP2',
            2 => 'SeatBookingP1',
            3 => 'SeatBooking',
            4 => 'SeatBookingRh'
        ];

        return response()->json([
            'departement_id' => $collaborateur->departement_id,
            'component_name' => $componentMap[$collaborateur->departement_id] ?? 'SeatBooking'
        ]);
    }

    public function is_STL()
    {
        $collaborateurId = Auth::user()->id;
        $collaborateur = Collaborateur::with('roles')->find($collaborateurId);

        $isSTL = $collaborateur->roles->contains('name', 'STL');

        return response()->json(['is_STL' => $isSTL]);
    }

    public function getDashboardType()
    {
        $collaborateurId = Auth::user()->id;

        $collaborateur = Collaborateur::with('roles')->find($collaborateurId);

        $roleName = $collaborateur->roles->first()->name ?? 'Collaborateur';

        $dashboard = [
            'RH' => 'Dashboard-RH',
            'STL' => 'Dashboard-STL',
            'TL' => 'Dashboard-TL',
            'Collaborateur' => 'Dashboard-Collab'
        ][$roleName] ?? 'Dashboard-Collab';

        return response()->json([
            'component_name' => $dashboard,
            'role' => $roleName
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
