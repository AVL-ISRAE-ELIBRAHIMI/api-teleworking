<?php

namespace Database\Seeders;

use App\Models\Collaborateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollaborateurSeeder extends Seeder
{
    // public function run()
    // {
    //     // URL de la ressource JSON
    //     $url = 'https://id.avl.com/v2/resources/?filter=/Person%5B(UserUI=true)%20and%20(starts-with(AccountName,%22%25AVL/MA%22)%20or%20starts-with(DisplayName,%22%25AVL/MA%22))%20and%20(not(starts-with(AccountName,%22AVL/MA%22)%20or%20starts-with(DisplayName,%22AVL/MA%22)))%5D&attributes=DisplayName,Email,AccountName,JobTitle,Manager,EmployeeState&pageSize=1000&index=0';
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NEGOTIATE);
    //     curl_setopt($ch, CURLOPT_USERPWD, ":");
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //     $response = curl_exec($ch);
    //     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     $error = curl_error($ch);
    //     curl_close($ch);

    //         if ($response === false) {
    //             throw new \Exception("Failed to fetch data: $error");
    //         }  

    //     $data = json_decode($response, true);


    //     foreach ($data['Results'] as $person) {
    //         // Vérifie que DisplayName est présent
    //         if (!isset($person['DisplayName'])) continue;
    //         // Séparation Nom / Prénom
    //         $displayNameParts = explode(',', $person['DisplayName']);
    //         $lastName = trim($displayNameParts[0]);
    //         $firstName = trim(explode(' ,', $displayNameParts[1] ?? '')[0]);

    //         // Insertion
    //         Collaborateur::updateOrCreate(
    //             ['id' => $person['ObjectID']],
    //             [
    //                 'nom' => $lastName,
    //                 'prenom' => $firstName,
    //                 'email' => $person['Email'] ?? null,
    //                 'manager' => $person['Manager'] ?? null,
    //                 'activity' => $person['JobTitle'] ?? null,
    //             ]
    //         );
    //     }
    //     Collaborateur::whereNull('manager')->delete();

    // }
    public function run()
    {
        $url = 'https://id.avl.com/v2/resources/?filter=/Person%5B(UserUI=true)%20and%20(starts-with(AccountName,%22%25AVL/MA%22)%20or%20starts-with(DisplayName,%22%25AVL/MA%22))%20and%20(not(starts-with(AccountName,%22AVL/MA%22)%20or%20starts-with(DisplayName,%22AVL/MA%22)))%5D&attributes=DisplayName,Email,AccountName,JobTitle,Manager,EmployeeState&pageSize=1000&index=0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NEGOTIATE);
        curl_setopt($ch, CURLOPT_USERPWD, ":");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            throw new \Exception("Failed to fetch data: {$error} (HTTP {$httpCode})");
        }

        $data = json_decode($response, true);

        foreach ($data['Results'] as $person) {
            if (!isset($person['DisplayName'], $person['ObjectID'])) {
                continue;
            }

            // Séparation Nom / Prénom
            $displayNameParts = explode(',', $person['DisplayName']);
            $lastName  = trim($displayNameParts[0]);
            $firstName = trim(explode(' ,', $displayNameParts[1] ?? '')[0]);

            // Récup manager (on suppose que Manager = id AVL du manager)
            $managerIdFromApi = $person['Manager'] ?? null;
            $manager = null;
            if ($managerIdFromApi) {
                $manager = Collaborateur::find($managerIdFromApi);
            }

            // On regarde s’il existe déjà
            $existing = Collaborateur::find($person['ObjectID']);

            // Insertion / update
            $collab = Collaborateur::updateOrCreate(
                ['id' => $person['ObjectID']],
                [
                    'nom'      => $lastName,
                    'prenom'   => $firstName,
                    'email'    => $person['Email'] ?? null,
                    'manager'  => $managerIdFromApi,
                    'activity' => $person['JobTitle'] ?? null,

                    // si tu veux écraser à chaque sync :
                    'departement_id' => $manager?->departement_id,
                    'equipe_id'      => $manager?->equipe_id,
                ]
            );
            // Nettoyage des collaborateurs sans manager
            Collaborateur::whereNull('manager')->delete();

            //Enlever AVL/MA de prenom 
            DB::table('collaborateurs')->update([
                'prenom' => DB::raw("REPLACE(prenom, 'AVL/MA', '')")
            ]);

            // Si tu veux ajouter le rôle 4 seulement pour les nouveaux :
            if (!$existing) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id'    => 4,
                        'model_type' => Collaborateur::class,
                        'model_id'   => $collab->id,
                    ],
                    []
                );
            }
        }
    }
}
