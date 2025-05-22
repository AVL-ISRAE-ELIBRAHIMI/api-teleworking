<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ListUserReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $reservationService;

    public function __construct(ListUserReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index()
    {

        $collaborateurId = session('user.id');
        if (!$collaborateurId) {
            return response()->json(['error' => 'Collaborateur non identifié'], 401);
        }
    
        $reservations = $this->reservationService->getReservationsByCollaborateur($collaborateurId);
    
        return response()->json($reservations);
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
