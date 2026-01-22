<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use App\Models\Place;
use App\Services\ListRHTeamReservationService;
use App\Services\ListSkillTeamReservationService;
use App\Services\ListTeamReservationService;
use App\Services\ListUserReservationService;
use Illuminate\Http\Request;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    public function getOfficeLayout()
    {
        return response()->json($this->reservationService->getOfficeLayout());
    }

    public function getPlaces($departement_id = null)
    {
        try {
            $places = $this->reservationService->getPlaces($departement_id);
            return response()->json(['places' => $places]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch places',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSeatBookingType()
    {
        try {
            return response()->json($this->reservationService->getSeatBookingType());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function is_STL()
    {
        return response()->json(['is_STL' => $this->reservationService->isSTL()]);
    }

    public function getDashboardType()
    {
        return response()->json($this->reservationService->getDashboardType());
    }


    public function deleteDates(Request $request, ReservationService $service)
    {
        $request->validate([
            'place_label' => 'required|string',
            'dates' => 'required|array',
            'dates.*' => 'date_format:d-m-Y',
        ]);

        $user = Auth::user(); // utilisateur connecté

        $place = $service->softDeleteUserDates(
            $user,
            $request->place_label,
            $request->dates
        );

        if (!$place) {
            return response()->json([
                'message' => 'Place not found for user department'
            ], 404);
        }

        return response()->json([
            'message' => 'Dates soft-deleted successfully',
            'deleted_dates' => $request->dates,
        ]);
    }

    public function override(Request $request, ReservationService $service)
    {
        $request->validate([
            'collaborator' => 'required|string',
            'seats' => 'required|string',
            'dates' => 'required|array',
            'motif' => 'required|integer',
            'justification' => 'required|string',
        ]);

        // 1️⃣ Récupérer collaborateur
        $collaborator = Collaborateur::whereRaw("CONCAT(nom, ' ', prenom) = ?", [$request->collaborator])
            ->firstOrFail();

       
        // 2️⃣ Trouver la place exacte
        $place = Place::where('name', $request->seats)
            ->where('departement_id', $collaborator->departement_id)
            ->firstOrFail();

        // 3️⃣ ID du Skill Team Leader connecté
        $requestedBy = Auth::user()->id;
       
        // 4️⃣ Appeler service
        $service->createOverride(
            $collaborator->id,
            $place->id,
            $request->dates,
            $request->motif,
            $request->justification,
            $requestedBy
        );

        return response()->json(['message' => 'Override request created successfully']);
    }
}
