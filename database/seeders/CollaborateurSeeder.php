<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Teleworking\Collaborateur;

class CollaborateurSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $url = 'https://id.avl.com/v2/resources/?filter=/Person%5B(UserUI=true)%20and%20(starts-with(AccountName,%22%25AVL/MA%22)%20or%20starts-with(DisplayName,%22%25AVL/MA%22))%20and%20(not(starts-with(AccountName,%22AVL/MA%22)%20or%20starts-with(DisplayName,%22AVL/MA%22)))%5D&attributes=DisplayName,Email,AccountName,JobTitle,Manager,EmployeeState&pageSize=1000&index=0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NEGOTIATE);
        curl_setopt($ch, CURLOPT_USERPWD, ":");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            throw new \Exception("Failed to fetch data: {$error} (HTTP {$httpCode})");
        }

        $data = json_decode($response, true);

        if (!isset($data['Results']) || !is_array($data['Results'])) {
            throw new \Exception('Invalid API response format.');
        }

        foreach ($data['Results'] as $person) {
            if (!isset($person['DisplayName'], $person['ObjectID'])) {
                continue;
            }

            $displayNameParts = explode(',', $person['DisplayName']);
            $lastName = trim($displayNameParts[0] ?? '');
            $firstName = trim($displayNameParts[1] ?? '');

            $managerIdFromApi = $person['Manager'] ?? null;
            $manager = $managerIdFromApi ? Collaborateur::find($managerIdFromApi) : null;

            Collaborateur::firstOrCreate(
                ['id' => $person['ObjectID']],
                [
                    'nom' => $lastName,
                    'prenom' => $firstName,
                    'email' => $person['Email'] ?? null,
                    'manager' => $managerIdFromApi,
                    'activity' => $person['JobTitle'] ?? null,
                    'departement_id' => $manager?->departement_id,
                    'equipe_id' => $manager?->equipe_id,
                ]
            );
        }

        DB::table('collaborateurs')->update([
            'prenom' => DB::raw("REPLACE(prenom, 'AVL/MA', '')")
        ]);

        Collaborateur::whereNull('manager')->delete();

        $role = Role::findById(4);

        foreach (Collaborateur::all() as $collab) {
            $collab->syncRoles([$role]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}