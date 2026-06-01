<?php

namespace App\Http\Controllers\API\Teleworking;

use App\Http\Controllers\Controller;
use App\Services\Teleworking\DepartementService;
use Illuminate\Http\JsonResponse;


class DepartementController extends Controller
{

    protected DepartementService $departementService;

    public function __construct(DepartementService $departementService)
    {
        $this->departementService = $departementService;
    }

    public function reservationsStats(): JsonResponse
    {
        try {
            $result = $this->departementService->reservationsStats();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du calcul des statistiques',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reservationsStatsSTL(): JsonResponse
    {
        try {
            $result = $this->departementService->reservationsStatsSTL();
            return response()->json($result);
        } catch (\Exception $e) {
            $status = $e->getMessage() === 'Accès non autorisé' ? 403 : 500;
            return response()->json([
                'error' => $e->getMessage(),
            ], $status);
        }
    }
}
