<?php

namespace App\Services;

use App\Models\Collaborateur;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class WindowsAuthService
{
    protected $windowsAuthUrl = "https://id.avl.com/v2/currentuser/?locale=en-US";

    public function authenticateUser()
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->windowsAuthUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NEGOTIATE);
        curl_setopt($ch, CURLOPT_USERPWD, ":");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("Failed to fetch data: $error");
        }

        $data = json_decode($response, true);
        
        if (!empty($data)) {
            // Stocker les données importantes dans la session
            Session::put('user.display_name', $data['DisplayName'] ?? null);
            Session::put('user.email', $data['Email'] ?? null);
            Session::put('user.job_title', $data['JobTitle'] ?? null);
            Session::put('user.id', $data['ObjectID'] ?? null);
            // Trouver le collaborateur dans la base de données
            $collaborateur = Collaborateur::where('id', $data['ObjectID'] ?? null)->first();
            Session::put('user.departement_id', $collaborateur ? $collaborateur->departement_id : null);
        
        
            return [
                'displayName' => $data['DisplayName'] ?? null,
                'email' => $data['Email'] ?? null,
                'jobTitle' => $data['JobTitle'] ?? null,
                'id' => $data['ObjectID'] ?? null,
                'departement_id' => $collaborateur ? $collaborateur->departement_id : null,
                'httpCode' => $httpCode,
            ];
        }

        throw new \Exception('Invalid response data');
    }
}
