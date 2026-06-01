<?php

namespace App\Http\Controllers\API\Teleworking;

use App\Http\Controllers\Controller;
use App\Services\Teleworking\CollaborateurService;
use Illuminate\Http\Request;

class CollaborateurController extends Controller
{
      protected CollaborateurService $collaborateurService;

    public function __construct(CollaborateurService $collaborateurService)
    {
        $this->collaborateurService = $collaborateurService;
    }

    public function getUserRole()
    {
        $role = $this->collaborateurService->getUserRole();
        return response()->json(['role' => $role]);
    }

    public function updateQuota(Request $request, string $id)
    {
        $result = $this->collaborateurService->updateQuota($request, $id);

        return response()->json([
            'id' => $result['id'],
            'quota' => $result['quota'],
        ], $result['status']);
    }

    public function quotaReturn()
    {
        return response()->json($this->collaborateurService->quotaReturn());
    }
   
}
