<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
