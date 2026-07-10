<?php

namespace App\Http\Controllers\API\TAM;

use App\Services\TAM\MaterielService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;


class MaterielController extends Controller
{
    protected  MaterielService $materielService;

    public function __construct(MaterielService $materielService)
    {
        $this->materielService = $materielService;
    }

    /**
     * Retourner les matériels disponibles
     *
     * @return JsonResponse
     */
    public function available_assets(): JsonResponse
    {
        $materiels = $this->materielService->getAvailableMateriels();

        return response()->json([
            'status' => 'success',
            'data'   => $materiels
        ]);
    }
}
