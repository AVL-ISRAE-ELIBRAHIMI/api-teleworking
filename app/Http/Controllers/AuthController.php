<?php

namespace App\Http\Controllers;

use App\Services\WindowsAuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $windowsAuthService;

    public function __construct(WindowsAuthService $windowsAuthService)
    {
        $this->windowsAuthService = $windowsAuthService;
    }

    public function login()
    {
        try {
            $userData = $this->windowsAuthService->authenticateUser();


            return response()->json([
                'success' => true,
                'data' => $userData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
