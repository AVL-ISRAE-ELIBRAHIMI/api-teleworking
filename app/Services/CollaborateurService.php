<?php

namespace App\Services;

use App\Models\Collaborateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CollaborateurService
{
    /**
     * Récupère le rôle du collaborateur connecté
     */
    public function getUserRole()
    {
        $collaborateurId = Auth::user()->id;
        $collaborateur = Collaborateur::with('roles')->find($collaborateurId);

        return $collaborateur->roles->first()->name ?? null;
    }

    /**
     * Met à jour le quota d’un collaborateur (par ID)
     */
    public function updateQuota(Request $request, string $id)
    {
        // Validation des données
        $data = $request->validate([
            'quota' => ['required', 'integer', 'min:0', 'max:22'],
        ]);

        // Recherche du collaborateur
        $collab = Collaborateur::findOrFail($id);

        // Mise à jour du quota
        $collab->quota = $data['quota'];
        $collab->save();

        return [
            'id'    => $collab->id,
            'quota' => $collab->quota,
            'status' => Response::HTTP_OK,
        ];
    }

    /**
     * Retourne le composant Vue correspondant à la gestion des quotas
     */
    public function quotaReturn()
    {
        $collaborateurId = Auth::user()->id;
        $collaborateur = Collaborateur::with('roles')->find($collaborateurId);

        $roleName = $collaborateur->roles->first()->name ?? 'Collaborateur';

        $dashboard = [
            'RH' => 'GestionQuota-RH',
            'STL' => 'GestionQuota',
        ][$roleName] ?? 'GestionQuota';

        return [
            'component_name' => $dashboard,
            'role' => $roleName,
        ];
    }
}
