<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Collaborator;
use App\Models\Reservation;
use Symfony\Component\HttpFoundation\Response;

class CollaborateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getUserRole()
    {
        $collaborateurId = Auth::user()->id;

        $collaborateur = Collaborateur::with('roles')->find($collaborateurId);

        $role = $collaborateur->roles->first()->name ?? null;

        return response()->json([
            'role' => $role
        ]);
    }


    public function updateQuota(Request $request, string $id)
    {
        // ✅ Validation
        $data = $request->validate([
            'quota' => ['required', 'integer', 'min:0', 'max:22'], // bornes métier
        ]);

        // ✅ Recherche du collaborateur (UUID)
        $collab = Collaborateur::findOrFail($id);

        // ✅ Mise à jour du quota
        $collab->quota = $data['quota'];
        $collab->save();

        // ✅ Réponse JSON
        return response()->json([
            'id'    => $collab->id,
            'quota' => $collab->quota,
        ], Response::HTTP_OK);
    }

    public function quotaReturn()
    {
        $collaborateurId = Auth::user()->id;

        $collaborateur = Collaborateur::with('roles')->find($collaborateurId);

        $roleName = $collaborateur->roles->first()->name ?? 'Collaborateur';

        $dashboard = [
            'RH' => 'GestionQuota-RH',
            'STL' => 'GestionQuota',

        ][$roleName] ?? 'GestionQuota';

        return response()->json([
            'component_name' => $dashboard,
            'role' => $roleName
        ]);
    }

    public function index()
    {
        //
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
