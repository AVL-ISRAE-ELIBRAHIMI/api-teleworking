<?php

namespace App\Http\Controllers\API\TAM;

use App\Http\Controllers\Controller;
use App\Services\TAM\RequestAssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RequestAssetController extends Controller
{
    public function __construct(
        protected RequestAssetService $requestAssetService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'materiel_id' => ['required', 'integer'],
            'requestor' => ['required', 'string', 'max:255'],
            'borrow_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:borrow_date'],
            'new_location' => ['required', 'string', 'max:255'],
            'remark' => ['nullable', 'string'],
        ]);

        $requestAsset = $this->requestAssetService->create($validated, $request);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'data' => $requestAsset,
        ], 201);
    }
    public function reservedPeriods(int $materiel): JsonResponse
    {
        $periods = $this->requestAssetService->reservedPeriods($materiel);

        return response()->json([
            'periods' => $periods,
        ]);
    }


    public function collaboratorRequests(): JsonResponse
    {
        $requests = $this->requestAssetService->collaboratorRequests();

        return response()->json([
            'requests' => $requests,
        ]);
    }
}
