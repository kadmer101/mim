<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\WebBloc;
use App\Services\DatabaseConnectionService;

class WebBlocService
{
    protected $dbService;

    public function __construct(DatabaseConnectionService $dbService)
    {
        $this->dbService = $dbService;
    }

    /**
     * Get WebBlocs of a specific type with pagination and filtering
     */
    public function getWebBlocs(string $type, array $params, int $websiteId): array
    {
        $this->dbService->connectToWebsite($websiteId);

        $query = DB::connection('sqlite_website')
            ->table('web_blocs')
            ->where('webbloc_type', $type);

        // Apply filters
        $this->applyFilters($query, $params);

        // Get total count before pagination
        $total = $query->count();

        // Apply sorting
        $this->applySorting($query, $params['sort'] ?? 'newest');

        // Apply pagination
        $limit = min($params['limit'] ?? 10, 100);
        $offset = $params['offset'] ?? 0;

        $webBlocs = $query->limit($limit)->offset($offset)->get();

        // Format results
        $formatted = $webBlocs->map(function ($webBloc) {
            return $this->formatWebBlocData($webBloc);
        });

        return [
            'data' => $formatted->toArray(),
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ]
        ];
    }

    /**
     * Get a single WebBloc by ID and type
     */
    public function getWebBloc(string $type, int $id, int $websiteId): ?array
    {
        $this->dbService->connectToWebsite($websiteId);

        $webBloc = DB::connection('sqlite_website')
            ->table('web_blocs')
            ->where('id', $id)
            ->where('webbloc_type', $type)
            ->first();

        return $webBloc ? $this->formatWebBlocData($webBloc) : null;
    }

    /**
     * Create a new WebBloc
     */
    public function createWebBloc(array $data, int $websiteId): array
    {
        $this->dbService->connectToWebsite($websiteId);

        // Validate and sanitize data
        $sanitizedData = $this->sanitizeWebBlocData($data);

        // Prepare data for insertion
        $insertData = [
            'webbloc_type' => $sanitizedData['webbloc_type'],
            'user_id' => $sanitizedData['user_id'] ?? null,
            'page_url' => $sanitizedData['page_url'],
            'data' => json_encode($sanitizedData['data']),
            'metadata' => json_encode($sanitizedData['metadata'] ?? []),
            'status' => $sanitizedData['status'] ?? 'active',
            'parent_id' => $sanitizedData['parent_id'] ?? null,
            'sort_order' => $sanitizedData['sort_order'] ?? 0,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $id = DB::connection('sqlite_website')
            ->table('web_blocs')
            ->insertGetId($insertData);

        $webBloc = DB::connection('sqlite_website')
            ->table('web_blocs')
            ->find($id);

        return $this->formatWebBlocData($webBloc);
    }

    /**
     * Update an existing WebBloc
     */
    public function updateWebBloc(int $id, array $data, int $websiteId): ?array
    {
        $this->dbService->connectToWebsite($websiteId);

        // Get existing WebBloc
        $existing = DB::connection('sqlite_website')
            ->table('web_blocs')
            ->find($id);

        if (!$existing) {
            return null;
        }

        // Sanitize update data
        $sanitizedData = $this->sanitizeWebBlocData($data, $existing);

        // Prepare update data
        $updateData = [
            'updated_at' => now()
        ];

        if (isset($sanitizedData['data'])) {
            $updateData['data'] = json_encode($sanitizedData['data']);
        }

        if (isset($sanitizedData['metadata'])) {
            $updateData['metadata'] = json_encode($sanitizedData['metadata']);
        }

        if (isset($sanitizedData['status'])) {
            $updateData['status'] = $sanitizedData['status'];
        }

        if (isset($sanitizedData['sort_order'])) {
            $updateData['sort_order'] = $sanitizedData['sort_order'];
        }

        // Perform update
        DB::connection('sqlite_website')
            ->table('web_blocs')
            ->where('id', $id)
            ->update($updateData);

        // Return updated WebBloc
        $updated = DB::connection('sqlite_website')
            ->table('web_blocs')
            ->find($id);

        return $this->formatWebBlocData($updated);
    }

    /**
     * Delete a WebBloc
     */
    public function deleteWebBloc(int $id, string $type, int $websiteId): bool
    {
        $this->dbService->connectToWebsite($websiteId);

        $deleted = DB::connection('sqlite_website')
            ->table('web_blocs')
            ->where('id', $id)
            ->where('webbloc_type', $type)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Render WebBloc as HTML
     */
    public function renderWebBloc(string $type, array $params, int $websiteId): string
    {
        // Get WebBlocs data
        $result = $this->getWebBlocs($type, $params, $websiteId);
        
        // Get theme
        $theme = $params['theme'] ?? 'default';
        
        // Render HTML template
        return $this->renderTemplate($type, $result['data'], $theme);
    }

    /**
     * Check if WebBloc type exists
     */
    public function typeExists(string $type): bool
    {
        $allowedTypes = $this->getAllowedTypes();
        return in_array($type, $allowedTypes);
    }

    /**
     * Check if type allows creation
     */
    public function canCreate(string $type): bool
    {
        $typeDefinition = $this->getTypeMetadata($type);
        return $typeDefinition['crud']['create'] ?? false;
    }

    /**
     * Check if type allows updates
     */
    public function canUpdate(string $type): bool
    {
        $typeDefinition = $this->getTypeMetadata($type);
        return $typeDefinition['crud']['update'] ?? false;
    }

    /**
     * Check if type allows deletion
     */
    public function canDelete(string $type): bool
    {
        $typeDefinition = $this->getTypeMetadata($type);
        return $typeDefinition['crud']['delete'] ?? false;
    }

    /**
     * Get metadata for a WebBloc type
     */
    public function getTypeMetadata(string $type): ?array
    {
        $cacheKey = "webbloc_type_metadata:{$type}";
        
        return Cache::remember($cacheKey, 3600, function () use ($type) {
            return DB::table('web_blocs')
                ->where('type', $type)
                ->first()?->metadata ?? null;
        });
    }

    /**
     * Get all allowed WebBloc types
     */
    private function getAllowedTypes(): array
    {
        return Cache::remember('webbloc_allowed_types', 3600, function () {
            return DB::table('web_blocs')
                ->where('is_active', true)
                ->pluck('type')
                ->toArray();
        });
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $params): void
    {
        // Page URL filter
        if (!empty($params['page_url'])) {
            $query->where('page_url', $params['page_url']);
        }

        // Status filter
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // User ID filter
        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        // Parent ID filter
        if (isset($params['parent_id'])) {
            if ($params['parent_id'] === null || $params['parent_id'] === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $params['parent_id']);
            }
        }

        // Search filter
        if (!empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $query->where('data', 'LIKE', $search);
        }

        // Custom filters
        if (!empty($params['filters']) && is_array($params['filters'])) {
            foreach ($params['filters'] as $key => $value) {
                if (is_array($value)) {
                    $query->whereIn($key, $value);
                } else {
                    $query->where($key, $value);
                }
            }
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, string $sort): void
    {
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;

            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'updated':
                $query->orderBy('updated_at', 'desc');
                break;

            case 'sort_order':
                $query->orderBy('sort_order', 'asc')
                      ->orderBy('created_at', 'desc');
                break;

            case 'random':
                $query->inRandomOrder();
                break;

            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    /**
     * Format WebBloc data for response
     */
    private function formatWebBlocData($webBloc): array
    {
        return [
            'id' => $webBloc->id,
            'type' => $webBloc->webbloc_type,
            'page_url' => $webBloc->page_url,
            'data' => json_decode($webBloc->data, true),
            'metadata' => json_decode($webBloc->metadata ?? '{}', true),
            'status' => $webBloc->status,
            'user_id' => $webBloc->user_id,
            'parent_id' => $webBloc->parent_id,
            'sort_order' => $webBloc->sort_order,
            'created_at' => $webBloc->created_at,
            'updated_at' => $webBloc->updated_at
        ];
    }

    /**
     * Sanitize WebBloc data
     */
    private function sanitizeWebBlocData(array $data, $existing = null): array
    {
        $sanitized = [];

        // Copy allowed fields
        $allowedFields = ['webbloc_type', 'user_id', 'page_url', 'data', 'metadata', 'status', 'parent_id', 'sort_order'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $sanitized[$field] = $data[$field];
            }
        }

        // Sanitize data field
        if (isset($sanitized['data']) && is_array($sanitized['data'])) {
            $sanitized['data'] = $this->sanitizeDataField($sanitized['data']);
        }

        return $sanitized;
    }

    /**
     * Sanitize data field content
     */
    private function sanitizeDataField(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove dangerous HTML tags but keep safe ones
                $sanitized[$key] = strip_tags($value, '<p><br><strong><em><u><ol><ul><li><a><blockquote>');
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeDataField($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Render HTML template for WebBloc
     */
    private function renderTemplate(string $type, array $data, string $theme): string
    {
        // Simple template rendering - in production, use a proper template engine
        $html = "<div class=\"webbloc webbloc-{$type} theme-{$theme}\">\n";
        
        foreach ($data as $item) {
            $html .= $this->renderItem($type, $item, $theme);
        }
        
        $html .= "</div>\n";

        return $html;
    }

    /**
     * Render individual WebBloc item
     */
    private function renderItem(string $type, array $item, string $theme): string
    {
        switch ($type) {
            case 'comment':
                return $this->renderComment($item, $theme);

            case 'review':
                return $this->renderReview($item, $theme);

            case 'testimonial':
                return $this->renderTestimonial($item, $theme);

            default:
                return $this->renderGeneric($item, $theme);
        }
    }

    /**
     * Render comment HTML
     */
    private function renderComment(array $item, string $theme): string
    {
        $data = $item['data'];
        $authorName = $data['author_name'] ?? 'Anonymous';
        $content = $data['content'] ?? '';
        $createdAt = $item['created_at'];

        return "
        <div class=\"webbloc-item comment-item\" data-id=\"{$item['id']}\">
            <div class=\"comment-header\">
                <strong class=\"author-name\">{$authorName}</strong>
                <time class=\"created-at\">{$createdAt}</time>
            </div>
            <div class=\"comment-content\">{$content}</div>
        </div>\n";
    }

    /**
     * Render review HTML
     */
    private function renderReview(array $item, string $theme): string
    {
        $data = $item['data'];
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $rating = $data['rating'] ?? 0;
        $authorName = $data['author_name'] ?? 'Anonymous';
        $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

        return "
        <div class=\"webbloc-item review-item\" data-id=\"{$item['id']}\">
            <div class=\"review-header\">
                <h4 class=\"review-title\">{$title}</h4>
                <div class=\"review-rating\">{$stars}</div>
            </div>
            <div class=\"review-content\">{$content}</div>
            <div class=\"review-footer\">
                <span class=\"author-name\">by {$authorName}</span>
            </div>
        </div>\n";
    }

    /**
     * Render testimonial HTML
     */
    private function renderTestimonial(array $item, string $theme): string
    {
        $data = $item['data'];
        $content = $data['content'] ?? '';
        $authorName = $data['author_name'] ?? 'Anonymous';
        $authorTitle = $data['author_title'] ?? '';
        $authorCompany = $data['author_company'] ?? '';

        return "
        <div class=\"webbloc-item testimonial-item\" data-id=\"{$item['id']}\">
            <div class=\"testimonial-content\">{$content}</div>
            <div class=\"testimonial-author\">
                <strong class=\"author-name\">{$authorName}</strong>
                {$authorTitle} {$authorCompany}
            </div>
        </div>\n";
    }

    /**
     * Render generic WebBloc HTML
     */
    private function renderGeneric(array $item, string $theme): string
    {
        $data = json_encode($item['data']);
        
        return "
        <div class=\"webbloc-item generic-item\" data-id=\"{$item['id']}\">
            <pre>{$data}</pre>
        </div>\n";
    }
}