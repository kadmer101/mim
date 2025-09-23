<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WebBlocRequest;
use App\Http\Resources\WebBlocResource;
use App\Models\Website;
use App\Models\WebBloc;
use App\Services\WebBlocService;
use App\Services\DatabaseConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class WebBlocController extends Controller
{
    protected $webBlocService;
    protected $dbService;

    public function __construct(WebBlocService $webBlocService, DatabaseConnectionService $dbService)
    {
        $this->webBlocService = $webBlocService;
        $this->dbService = $dbService;
    }

    /**
     * List WebBlocs of a specific type
     */
    public function index(Request $request, string $type)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            // Validate WebBloc type exists
            if (!$this->webBlocService->typeExists($type)) {
                return $this->errorResponse("WebBloc type '{$type}' not found", 404);
            }

            $this->dbService->connectToWebsite($website->id);

            // Build query parameters
            $params = [
                'page_url' => $request->input('page_url'),
                'limit' => min($request->input('limit', 10), 100),
                'offset' => $request->input('offset', 0),
                'sort' => $request->input('sort', 'newest'),
                'status' => $request->input('status', 'active'),
                'user_id' => $request->input('user_id'),
                'parent_id' => $request->input('parent_id'),
                'search' => $request->input('search'),
                'filters' => $request->input('filters', [])
            ];

            // Generate cache key
            $cacheKey = "webblocs:{$website->id}:{$type}:" . md5(serialize($params));
            
            $result = Cache::remember($cacheKey, 300, function () use ($type, $params, $website) {
                return $this->webBlocService->getWebBlocs($type, $params, $website->id);
            });

            // Log request
            $this->logRequest($website, 'list', $type);

            return $this->successResponse($result, "WebBlocs retrieved successfully");

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve WebBlocs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific WebBloc
     */
    public function show(Request $request, string $type, int $id)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            if (!$this->webBlocService->typeExists($type)) {
                return $this->errorResponse("WebBloc type '{$type}' not found", 404);
            }

            $this->dbService->connectToWebsite($website->id);

            $webBloc = $this->webBlocService->getWebBloc($type, $id, $website->id);
            
            if (!$webBloc) {
                return $this->errorResponse('WebBloc not found', 404);
            }

            $this->logRequest($website, 'show', $type);

            return $this->successResponse($webBloc, "WebBloc retrieved successfully");

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve WebBloc: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new WebBloc
     */
    public function store(WebBlocRequest $request, string $type)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            if (!$this->webBlocService->typeExists($type)) {
                return $this->errorResponse("WebBloc type '{$type}' not found", 404);
            }

            // Check if type allows creation
            if (!$this->webBlocService->canCreate($type)) {
                return $this->errorResponse("Creation not allowed for WebBloc type '{$type}'", 403);
            }

            $this->dbService->connectToWebsite($website->id);

            // Get user from token if provided
            $user = $this->getUserFromToken($request, $website);

            $data = array_merge($request->validated(), [
                'webbloc_type' => $type,
                'user_id' => $user ? $user->id : null,
                'page_url' => $request->input('page_url', ''),
                'metadata' => $request->input('metadata', [])
            ]);

            $webBloc = $this->webBlocService->createWebBloc($data, $website->id);

            // Clear cache
            $this->clearWebBlocCache($website->id, $type);

            $this->logRequest($website, 'create', $type);

            return $this->successResponse($webBloc, "WebBloc created successfully", 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create WebBloc: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a WebBloc
     */
    public function update(WebBlocRequest $request, string $type, int $id)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            if (!$this->webBlocService->typeExists($type)) {
                return $this->errorResponse("WebBloc type '{$type}' not found", 404);
            }

            if (!$this->webBlocService->canUpdate($type)) {
                return $this->errorResponse("Update not allowed for WebBloc type '{$type}'", 403);
            }

            $this->dbService->connectToWebsite($website->id);

            $user = $this->getUserFromToken($request, $website);
            
            // Check ownership if user is provided
            if ($user) {
                $existingWebBloc = DB::connection('sqlite_website')
                    ->table('web_blocs')
                    ->where('id', $id)
                    ->where('webbloc_type', $type)
                    ->first();

                if ($existingWebBloc && $existingWebBloc->user_id != $user->id) {
                    return $this->errorResponse('Unauthorized to update this WebBloc', 403);
                }
            }

            $webBloc = $this->webBlocService->updateWebBloc($id, $request->validated(), $website->id);

            if (!$webBloc) {
                return $this->errorResponse('WebBloc not found', 404);
            }

            $this->clearWebBlocCache($website->id, $type);
            $this->logRequest($website, 'update', $type);

            return $this->successResponse($webBloc, "WebBloc updated successfully");

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update WebBloc: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a WebBloc
     */
    public function destroy(Request $request, string $type, int $id)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            if (!$this->webBlocService->typeExists($type)) {
                return $this->errorResponse("WebBloc type '{$type}' not found", 404);
            }

            if (!$this->webBlocService->canDelete($type)) {
                return $this->errorResponse("Deletion not allowed for WebBloc type '{$type}'", 403);
            }

            $this->dbService->connectToWebsite($website->id);

            $user = $this->getUserFromToken($request, $website);
            
            // Check ownership if user is provided
            if ($user) {
                $existingWebBloc = DB::connection('sqlite_website')
                    ->table('web_blocs')
                    ->where('id', $id)
                    ->where('webbloc_type', $type)
                    ->first();

                if ($existingWebBloc && $existingWebBloc->user_id != $user->id) {
                    return $this->errorResponse('Unauthorized to delete this WebBloc', 403);
                }
            }

            $deleted = $this->webBlocService->deleteWebBloc($id, $type, $website->id);

            if (!$deleted) {
                return $this->errorResponse('WebBloc not found', 404);
            }

            $this->clearWebBlocCache($website->id, $type);
            $this->logRequest($website, 'delete', $type);

            return $this->successResponse([], "WebBloc deleted successfully");

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete WebBloc: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Render WebBloc as HTML component
     */
    public function render(Request $request, string $type)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return response('Invalid API key', 401);
            }

            if (!$this->webBlocService->typeExists($type)) {
                return response("WebBloc type '{$type}' not found", 404);
            }

            $this->dbService->connectToWebsite($website->id);

            $params = [
                'page_url' => $request->input('page_url'),
                'limit' => min($request->input('limit', 10), 100),
                'attributes' => $request->input('attributes', []),
                'theme' => $request->input('theme', 'default')
            ];

            $html = $this->webBlocService->renderWebBloc($type, $params, $website->id);

            $this->logRequest($website, 'render', $type);

            return response($html)->header('Content-Type', 'text/html');

        } catch (\Exception $e) {
            return response('Failed to render WebBloc: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get WebBloc metadata and configuration
     */
    public function metadata(Request $request, string $type)
    {
        try {
            $website = $this->getWebsiteFromRequest($request);
            if (!$website) {
                return $this->errorResponse('Invalid API key', 401);
            }

            $metadata = $this->webBlocService->getTypeMetadata($type);
            
            if (!$metadata) {
                return $this->errorResponse("WebBloc type '{$type}' not found", 404);
            }

            return $this->successResponse($metadata, "Metadata retrieved successfully");

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve metadata: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper methods
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

            return DB::connection('sqlite_website')->table('users')->find($userId);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function clearWebBlocCache($websiteId, $type)
    {
        $pattern = "webblocs:{$websiteId}:{$type}:*";
        $keys = Cache::getRedis()->keys($pattern);
        if ($keys) {
            Cache::getRedis()->del($keys);
        }
    }

    private function logRequest(Website $website, string $action, string $type)
    {
        try {
            $website->increment('total_requests');
            
            // Update statistics
            DB::table('website_statistics')
                ->where('website_id', $website->id)
                ->increment('total_webbloc_requests');

        } catch (\Exception $e) {
            // Log error but don't fail the request
        }
    }

    private function successResponse($data, $message = 'Success', $status = 200)
    {
        $responseFormat = $this->determineResponseFormat();
        
        if ($responseFormat === 'html') {
            if (is_string($data)) {
                return response($data)->header('Content-Type', 'text/html');
            }
            
            return response()->view('webbloc.response', [
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

    private function errorResponse($message, $status = 400)
    {
        $responseFormat = $this->determineResponseFormat();
        
        if ($responseFormat === 'html') {
            return response()->view('webbloc.error', [
                'message' => $message
            ])->header('Content-Type', 'text/html')->setStatusCode($status);
        }

        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }

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