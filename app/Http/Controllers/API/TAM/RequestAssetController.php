<?php

namespace App\Http\Controllers\API\TAM;

use App\Http\Controllers\Controller;
use App\Services\TAM\RequestAssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
    public function fetchRequests(): JsonResponse
    {
        $requests = $this->requestAssetService->fetchRequests();

        return response()->json([
            'requests' => $requests,
        ]);
    }

    public function accept($id): JsonResponse
    {
        $requestAsset = $this->requestAssetService->accept((int) $id);

        return response()->json([
            'message' => 'Request accepted successfully.',
            'data' => $requestAsset,
        ]);
    }

    public function refuse(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $requestAsset = $this->requestAssetService->refuse((int) $id, $validated['reason']);

        return response()->json([
            'message' => 'Request refused successfully.',
            'data' => $requestAsset,
        ]);
    }

   
}
