<?php

namespace App\Services;

use App\Models\Collaborateur;
use Illuminate\Support\Facades\Session;

class WindowsAuthService
{
    public function storeUserSession(array $userData): array
    {
        Session::put('user.id', $userData['objectId']);
        Session::put('user.display_name', $userData['displayName']);
        Session::put('user.email', $userData['email']);
        Session::put('user.job_title', $userData['jobTitle'] ?? null);

        $collaborateur = Collaborateur::where('id', $userData['objectId'])->first();
        Session::put('user.departement_id', $collaborateur ? $collaborateur->departement_id : null);

        return [
            'id' => $userData['objectId'],
            'displayName' => $userData['displayName'],
            'email' => $userData['email'],
            'jobTitle' => $userData['jobTitle'] ?? null,
            'departement_id' => $collaborateur ? $collaborateur->departement_id : null,
        ];
    }

    public function getCurrentUser(): ?array
    {
        if (!Session::has('user.id')) {
            return null;
        }

        return [
            'id' => Session::get('user.id'),
            'displayName' => Session::get('user.display_name'),
            'email' => Session::get('user.email'),
            'jobTitle' => Session::get('user.job_title'),
            'departement_id' => Session::get('user.departement_id'),
        ];
    }

    public function isAuthenticated(): bool
    {
        return Session::has('user.id');
    }

    public function clearUserSession(): void
    {
        Session::forget('user');
    }
}