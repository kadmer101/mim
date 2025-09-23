<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Website;
use App\Services\DatabaseConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $dbService;

    public function __construct(DatabaseConnectionService $dbService)
    {
        $this->dbService = $dbService;
    }

    /**
     * User registration endpoint
     */
    public function register(Request $request)
    {
        try {
            // Get website from API key
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            // Connect to website's SQLite database
            $this->dbService->connectToWebsite($website->id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8|confirmed',
                'metadata' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Check if user already exists
            $existingUser = DB::connection('sqlite_website')->table('users')
                ->where('email', $request->email)
                ->first();

            if ($existingUser) {
                return $this->errorResponse('User already exists with this email', 422);
            }

            // Create user
            $userId = DB::connection('sqlite_website')->table('users')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'metadata' => json_encode($request->metadata ?? []),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $user = DB::connection('sqlite_website')->table('users')->find($userId);

            // Generate token (simplified for SQLite)
            $token = base64_encode($user->id . ':' . $website->id . ':' . time());

            // Log registration activity
            $this->logActivity($website, 'user_registered', $user->id);

            return $this->successResponse([
                'user' => $this->formatUserData($user),
                'token' => $token
            ], 'User registered successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User login endpoint
     */
    public function login(Request $request)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            $this->dbService->connectToWebsite($website->id);

            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = DB::connection('sqlite_website')->table('users')
                ->where('email', $request->email)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            // Generate token
            $token = base64_encode($user->id . ':' . $website->id . ':' . time());

            // Update last login
            DB::connection('sqlite_website')->table('users')
                ->where('id', $user->id)
                ->update(['updated_at' => now()]);

            $this->logActivity($website, 'user_login', $user->id);

            return $this->successResponse([
                'user' => $this->formatUserData($user),
                'token' => $token
            ], 'Login successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User logout endpoint
     */
    public function logout(Request $request)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            // In a full implementation, you'd invalidate the token here
            $this->logActivity($website, 'user_logout', null);

            return $this->successResponse([], 'Logout successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            $user = $this->getUserFromToken($request, $website);
            if (!$user) {
                return $this->errorResponse('Invalid token', 401);
            }

            return $this->successResponse([
                'user' => $this->formatUserData($user)
            ], 'Profile retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Profile retrieval failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            $user = $this->getUserFromToken($request, $website);
            if (!$user) {
                return $this->errorResponse('Invalid token', 401);
            }

            $this->dbService->connectToWebsite($website->id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'avatar' => 'nullable|url',
                'metadata' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            DB::connection('sqlite_website')->table('users')
                ->where('id', $user->id)
                ->update([
                    'name' => $request->name,
                    'avatar' => $request->avatar,
                    'metadata' => json_encode($request->metadata ?? []),
                    'updated_at' => now()
                ]);

            $updatedUser = DB::connection('sqlite_website')->table('users')->find($user->id);

            return $this->successResponse([
                'user' => $this->formatUserData($updatedUser)
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get website from API key in request
     */
    private function getWebsiteFromRequest(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');
        
        if (!$apiKey) {
            return null;
        }

        return Website::whereHas('apiKeys', function ($query) use ($apiKey) {
            $query->where('key', $apiKey)->where('is_active', true);
        })->first();
    }

    /**
     * Get user from token
     */
    private function getUserFromToken(Request $request, Website $website)
    {
        $token = $request->bearerToken() ?? $request->input('token');
        
        if (!$token) {
            return null;
        }

        try {
            $decoded = base64_decode($token);
            [$userId, $websiteId, $timestamp] = explode(':', $decoded);

            if ($websiteId != $website->id) {
                return null;
            }

            $this->dbService->connectToWebsite($website->id);
            
            return DB::connection('sqlite_website')->table('users')->find($userId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format user data for response
     */
    private function formatUserData($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'metadata' => json_decode($user->metadata ?? '[]', true),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];
    }

    /**
     * Log activity
     */
    private function logActivity(Website $website, string $action, $userId = null)
    {
        try {
            $website->increment('total_requests');
            // Additional logging can be implemented here
        } catch (\Exception $e) {
            // Log error but don't fail the request
        }
    }

    /**
     * Success response helper
     */
    private function successResponse($data, $message = 'Success', $status = 200)
    {
        $responseFormat = $this->determineResponseFormat();
        
        if ($responseFormat === 'html') {
            return response()->view('webbloc.auth.success', [
                'data' => $data,
                'message' => $message
            ])->header('Content-Type', 'text/html');
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Error response helper
     */
    private function errorResponse($message, $status = 400)
    {
        $responseFormat = $this->determineResponseFormat();
        
        if ($responseFormat === 'html') {
            return response()->view('webbloc.auth.error', [
                'message' => $message
            ])->header('Content-Type', 'text/html')->setStatusCode($status);
        }

        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }

    /**
     * Validation error response helper
     */
    private function validationErrorResponse($errors)
    {
        $responseFormat = $this->determineResponseFormat();
        
        if ($responseFormat === 'html') {
            return response()->view('webbloc.auth.validation-error', [
                'errors' => $errors
            ])->header('Content-Type', 'text/html')->setStatusCode(422);
        }

        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }

    /**
     * Determine response format based on WebBloc configuration
     */
    private function determineResponseFormat()
    {
        $random = rand(1, 100);
        
        if ($random <= 75) {
            return 'html';
        } elseif ($random <= 90) {
            return 'json';
        } else {
            return 'other';
        }
    }
}