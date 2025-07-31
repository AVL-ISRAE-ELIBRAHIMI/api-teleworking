<?php

namespace App\Http\Controllers;

use App\Services\WindowsAuthService;
use App\Models\Collaborateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    protected $windowsAuthService;

    public function __construct(WindowsAuthService $windowsAuthService)
    {
        $this->windowsAuthService = $windowsAuthService;
    }

    /**
     * Fetch all company users from AVL resources endpoint
     */
    public function fetchCompanyUsers()
    {
        try {
            // Cache company users for 1 hour to avoid frequent API calls
            $companyUsers = Cache::remember('avl_company_users', 3600, function () {
                $resourcesUrl = "https://id.avl.com/v2/resources/?filter=/Person%5B(UserUI=true)%20and%20(starts-with(AccountName,%22%25AVL/MA%22)%20or%20starts-with(DisplayName,%22%25AVL/MA%22))%20and%20(not(starts-with(AccountName,%22AVL/MA%22)%20or%20starts-with(DisplayName,%22AVL/MA%22)))%5D&attributes=DisplayName,Domain,AccountName,JobTitle,OfficeLocation,OfficePhone,Email,EmployeeState&pageSize=10000&index=0";
                
                $response = Http::withoutVerifying()->timeout(30)->get($resourcesUrl);
                
                \Log::info('AVL API Response Status: ' . $response->status());
                \Log::info('AVL API Response Body: ' . $response->body());
                
                if ($response->successful()) {
                    $data = $response->json();
                    $allUsers = $data ?? []; // Response is directly an array
                    
                    \Log::info('Total users received: ' . count($allUsers));
                    
                    // Filter only users who have JobTitle (active employees)
                    $activeUsers = array_filter($allUsers, function($user) {
                        return isset($user['JobTitle']) && !empty($user['JobTitle']);
                    });
                    
                    \Log::info('Active users after filtering: ' . count($activeUsers));
                    
                    return array_values($activeUsers); // Reindex array
                }
                
                throw new \Exception('AVL API returned status: ' . $response->status() . ', Body: ' . $response->body());
            });

            return response()->json([
                'success' => true,
                'users' => $companyUsers,
                'count' => count($companyUsers)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch company users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate user using Windows credentials + company users matching
     */
    public function authenticateWithWindowsAndCompanyList(Request $request)
    {
        try {
            // Step 1: Extract Windows user information from request headers
            $windowsUser = $this->extractWindowsUserFromRequest($request);
            
            if (!$windowsUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Windows authentication found in request'
                ], 401);
            }

            // Step 2: Get company users list (only active employees with JobTitle)
            $companyUsers = Cache::remember('avl_company_users', 3600, function () {
                $resourcesUrl = "https://id.avl.com/v2/resources/?filter=/Person%5B(UserUI=true)%20and%20(starts-with(AccountName,%22%25AVL/MA%22)%20or%20starts-with(DisplayName,%22%25AVL/MA%22))%20and%20(not(starts-with(AccountName,%22AVL/MA%22)%20or%20starts-with(DisplayName,%22AVL/MA%22)))%5D&attributes=DisplayName,Domain,AccountName,JobTitle,OfficeLocation,OfficePhone,Email,EmployeeState&pageSize=10000&index=0";
                
                $response = Http::withoutVerifying()->timeout(30)->get($resourcesUrl);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $allUsers = $data ?? [];
                    
                    // Filter only users who have JobTitle (active employees)
                    $activeUsers = array_filter($allUsers, function($user) {
                        return isset($user['JobTitle']) && !empty($user['JobTitle']);
                    });
                    
                    return array_values($activeUsers);
                }
                
                \Log::error('AVL API failed in authenticateWithWindowsAndCompanyList - Status: ' . $response->status());
                return [];
            });

            // Step 3: Match Windows user against company list
            $matchedUser = $this->findUserInCompanyList($windowsUser, $companyUsers);
            
            if (!$matchedUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found in company directory'
                ], 403);
            }

            // Step 4: Authenticate and create session
            return $this->validateUser($request->merge([
                'objectId' => $matchedUser['ObjectID'],
                'displayName' => $matchedUser['DisplayName'],
                'email' => $matchedUser['Email'] ?? null,
                'jobTitle' => $matchedUser['JobTitle'] ?? null,
                'department' => null, // Not available in this API response
            ]));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract Windows user from request headers
     */
    private function extractWindowsUserFromRequest(Request $request)
    {
        // Method 1: Check for REMOTE_USER (IIS/Apache Windows auth)
        if ($request->server('REMOTE_USER')) {
            $remoteUser = $request->server('REMOTE_USER');
            return $this->parseWindowsUsername($remoteUser);
        }

        // Method 2: Check for AUTH_USER
        if ($request->server('AUTH_USER')) {
            $authUser = $request->server('AUTH_USER');
            return $this->parseWindowsUsername($authUser);
        }

        // Method 3: Check Authorization header for NTLM/Negotiate
        $authHeader = $request->header('Authorization');
        if ($authHeader && (str_contains($authHeader, 'NTLM') || str_contains($authHeader, 'Negotiate'))) {
            // This would require more complex NTLM/Kerberos token parsing
            // For now, return null and let frontend handle it
            return null;
        }

        // Method 4: Check for manually passed user (from frontend)
        if ($request->has('windowsUser')) {
            return $request->input('windowsUser');
        }

        return null;
    }

    /**
     * Parse Windows username formats
     */
    private function parseWindowsUsername($username)
    {
        // Handle formats like: DOMAIN\username, username@domain.com, or just username
        if (str_contains($username, '\\')) {
            $parts = explode('\\', $username);
            return [
                'domain' => $parts[0],
                'username' => $parts[1],
                'fullname' => $username
            ];
        } elseif (str_contains($username, '@')) {
            $parts = explode('@', $username);
            return [
                'username' => $parts[0],
                'domain' => $parts[1],
                'fullname' => $username
            ];
        } else {
            return [
                'username' => $username,
                'domain' => null,
                'fullname' => $username
            ];
        }
    }

    /**
     * Find user in company list by matching various fields
     */
    private function findUserInCompanyList($windowsUser, $companyUsers)
    {
        $searchUsername = strtolower($windowsUser['username']);
        
        foreach ($companyUsers as $companyUser) {
            // Match by AccountName (most reliable)
            if (isset($companyUser['AccountName'])) {
                $accountName = strtolower($companyUser['AccountName']);
                if ($accountName === $searchUsername || 
                    str_contains($accountName, $searchUsername) ||
                    str_contains($searchUsername, $accountName)) {
                    return $companyUser;
                }
            }

            // Match by Email prefix (before @)
            if (isset($companyUser['Email'])) {
                $emailPrefix = strtolower(explode('@', $companyUser['Email'])[0]);
                if ($emailPrefix === $searchUsername) {
                    return $companyUser;
                }
            }

            // Match by DisplayName (partial match)
            if (isset($companyUser['DisplayName'])) {
                $displayName = strtolower($companyUser['DisplayName']);
                if (str_contains($displayName, $searchUsername)) {
                    return $companyUser;
                }
            }
        }

        return null;
    }

    /**
     * Manual authentication with account lookup
     */
    public function authenticateWithAccountName(Request $request)
    {
        $request->validate([
            'accountName' => 'required|string',
        ]);

        try {
            // Get company users (only active employees with JobTitle)
            $companyUsers = Cache::remember('avl_company_users', 3600, function () {
                $resourcesUrl = "https://id.avl.com/v2/resources/?filter=/Person%5B(UserUI=true)%20and%20(starts-with(AccountName,%22%25AVL/MA%22)%20or%20starts-with(DisplayName,%22%25AVL/MA%22))%20and%20(not(starts-with(AccountName,%22AVL/MA%22)%20or%20starts-with(DisplayName,%22AVL/MA%22)))%5D&attributes=DisplayName,Domain,AccountName,JobTitle,OfficeLocation,OfficePhone,Email,EmployeeState&pageSize=10000&index=0";
                
                $response = Http::withoutVerifying()->timeout(30)->get($resourcesUrl);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $allUsers = $data ?? [];
                    
                    // Filter only users who have JobTitle (active employees)
                    $activeUsers = array_filter($allUsers, function($user) {
                        return isset($user['JobTitle']) && !empty($user['JobTitle']);
                    });
                    
                    return array_values($activeUsers);
                }
                
                return [];
            });

            // Find user by account name
            $matchedUser = null;
            foreach ($companyUsers as $user) {
                if (isset($user['AccountName']) && 
                    strtolower($user['AccountName']) === strtolower($request->accountName)) {
                    $matchedUser = $user;
                    break;
                }
            }

            if (!$matchedUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not found in company directory'
                ], 404);
            }

            // Create session with matched user
            return $this->validateUser($request->merge([
                'objectId' => $matchedUser['ObjectID'],
                'displayName' => $matchedUser['DisplayName'],
                'email' => $matchedUser['Email'] ?? null,
                'jobTitle' => $matchedUser['JobTitle'] ?? null,
                'department' => null, // Not available in this API response
            ]));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
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

    public function validateUser(Request $request)
    {
        $request->validate([
            'objectId' => 'required|string',
            'displayName' => 'required|string',
            'email' => 'required|email',
            'jobTitle' => 'nullable|string',
            'department' => 'nullable|string',
        ]);

        try {
            Session::put('user.id', $request->objectId);
            Session::put('user.display_name', $request->displayName);
            Session::put('user.email', $request->email);
            Session::put('user.job_title', $request->jobTitle);

            $collaborateur = Collaborateur::where('id', $request->objectId)->first();
            Session::put('user.departement_id', $collaborateur ? $collaborateur->departement_id : null);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $request->objectId,
                    'displayName' => $request->displayName,
                    'email' => $request->email,
                    'jobTitle' => $request->jobTitle,
                    'department' => $request->department,
                    'departement_id' => $collaborateur ? $collaborateur->departement_id : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCurrentUser()
    {
        if (!Session::has('user.id')) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => Session::get('user.id'),
                'displayName' => Session::get('user.display_name'),
                'email' => Session::get('user.email'),
                'jobTitle' => Session::get('user.job_title'),
                'departement_id' => Session::get('user.departement_id'),
            ]
        ]);
    }

    public function logout()
    {
        Session::forget('user');
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}