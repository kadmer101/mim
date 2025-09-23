<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\WebBlocService;
use Illuminate\Support\Str;

class WebBlocResource extends JsonResource
{
    protected $webBlocService;
    protected $format;
    protected $includeRelations;

    public function __construct($resource, WebBlocService $webBlocService = null, string $format = 'json', array $includeRelations = [])
    {
        parent::__construct($resource);
        $this->webBlocService = $webBlocService ?? app(WebBlocService::class);
        $this->format = $format;
        $this->includeRelations = $includeRelations;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'type' => $this->webbloc_type,
            'page_url' => $this->page_url,
            'data' => $this->formatData(),
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add optional fields based on format and requirements
        if ($this->format === 'full' || in_array('metadata', $this->includeRelations)) {
            $data['metadata'] = $this->formatMetadata();
        }

        if ($this->format === 'full' || in_array('user', $this->includeRelations)) {
            $data['user'] = $this->formatUser();
        }

        if ($this->format === 'full' || in_array('parent', $this->includeRelations)) {
            $data['parent'] = $this->formatParent();
        }

        if ($this->format === 'full' || in_array('children', $this->includeRelations)) {
            $data['children'] = $this->formatChildren();
        }

        // Add type-specific formatting
        $data = $this->applyTypeFormatting($data);

        // Add computed fields
        $data = $this->addComputedFields($data);

        return $data;
    }

    /**
     * Format the main data field
     */
    private function formatData(): array
    {
        $rawData = json_decode($this->data, true) ?? [];
        
        // Get type definition for proper formatting
        $typeDefinition = $this->webBlocService->getTypeMetadata($this->webbloc_type);
        
        if (!$typeDefinition) {
            return $rawData;
        }

        $formattedData = [];
        $attributes = $typeDefinition['attributes'] ?? [];

        foreach ($rawData as $key => $value) {
            $formattedData[$key] = $this->formatAttributeValue($key, $value, $attributes[$key] ?? []);
        }

        return $formattedData;
    }

    /**
     * Format individual attribute values
     */
    private function formatAttributeValue(string $key, $value, array $config)
    {
        $type = $config['type'] ?? 'string';

        switch ($type) {
            case 'date':
                return $value ? \Carbon\Carbon::parse($value)->toISOString() : null;

            case 'boolean':
                return (bool) $value;

            case 'integer':
                return (int) $value;

            case 'float':
                return (float) $value;

            case 'array':
                return is_array($value) ? $value : json_decode($value, true);

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;

            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;

            case 'html':
                // Sanitize HTML content
                return $this->sanitizeHtml($value);

            case 'markdown':
                // Convert markdown to HTML if needed
                return $this->convertMarkdown($value);

            default:
                return $value;
        }
    }

    /**
     * Format metadata field
     */
    private function formatMetadata(): array
    {
        $metadata = json_decode($this->metadata, true) ?? [];
        
        // Add system metadata
        $metadata['system'] = [
            'created_timestamp' => $this->created_at ? strtotime($this->created_at) : null,
            'updated_timestamp' => $this->updated_at ? strtotime($this->updated_at) : null,
        ];

        return $metadata;
    }

    /**
     * Format user information
     */
    private function formatUser(): ?array
    {
        if (!$this->user_id) {
            return null;
        }

        // In a real implementation, you'd load the user data
        // For now, return basic structure
        return [
            'id' => $this->user_id,
            'name' => $this->user_name ?? 'Anonymous',
            'avatar' => $this->user_avatar ?? null,
        ];
    }

    /**
     * Format parent WebBloc
     */
    private function formatParent(): ?array
    {
        if (!$this->parent_id) {
            return null;
        }

        return [
            'id' => $this->parent_id,
            'type' => $this->parent_type ?? null,
        ];
    }

    /**
     * Format children WebBlocs
     */
    private function formatChildren(): array
    {
        // In a real implementation, you'd load children data
        return [];
    }

    /**
     * Apply type-specific formatting
     */
    private function applyTypeFormatting(array $data): array
    {
        switch ($this->webbloc_type) {
            case 'comment':
                return $this->formatComment($data);

            case 'review':
                return $this->formatReview($data);

            case 'testimonial':
                return $this->formatTestimonial($data);

            case 'reaction':
                return $this->formatReaction($data);

            default:
                return $data;
        }
    }

    /**
     * Format comment-specific data
     */
    private function formatComment(array $data): array
    {
        if (isset($data['data']['content'])) {
            // Sanitize and format comment content
            $data['data']['content'] = $this->sanitizeHtml($data['data']['content']);
            
            // Add content preview
            $data['data']['content_preview'] = $this->generatePreview($data['data']['content']);
        }

        return $data;
    }

    /**
     * Format review-specific data
     */
    private function formatReview(array $data): array
    {
        if (isset($data['data']['rating'])) {
            // Ensure rating is numeric and within bounds
            $data['data']['rating'] = max(1, min(5, (int) $data['data']['rating']));
            
            // Add star representation
            $data['data']['stars'] = str_repeat('★', $data['data']['rating']) . str_repeat('☆', 5 - $data['data']['rating']);
        }

        return $data;
    }

    /**
     * Format testimonial-specific data
     */
    private function formatTestimonial(array $data): array
    {
        // Format author information
        if (isset($data['data']['author_name']) && isset($data['data']['author_title'])) {
            $data['data']['author_full'] = $data['data']['author_name'];
            if ($data['data']['author_title']) {
                $data['data']['author_full'] .= ', ' . $data['data']['author_title'];
            }
            if (isset($data['data']['author_company']) && $data['data']['author_company']) {
                $data['data']['author_full'] .= ' at ' . $data['data']['author_company'];
            }
        }

        return $data;
    }

    /**
     * Format reaction-specific data
     */
    private function formatReaction(array $data): array
    {
        if (isset($data['data']['type'])) {
            // Add emoji representation
            $emojis = [
                'like' => '👍',
                'love' => '❤️',
                'laugh' => '😂',
                'angry' => '😠',
                'sad' => '😢',
                'wow' => '😮'
            ];

            $data['data']['emoji'] = $emojis[$data['data']['type']] ?? '👍';
        }

        return $data;
    }

    /**
     * Add computed fields
     */
    private function addComputedFields(array $data): array
    {
        // Add age in human-readable format
        if ($this->created_at) {
            $data['age_human'] = \Carbon\Carbon::parse($this->created_at)->diffForHumans();
        }

        // Add URL-friendly slug if applicable
        if (isset($data['data']['title'])) {
            $data['slug'] = Str::slug($data['data']['title']);
        }

        // Add content statistics
        if (isset($data['data']['content'])) {
            $data['stats'] = [
                'word_count' => str_word_count(strip_tags($data['data']['content'])),
                'char_count' => strlen(strip_tags($data['data']['content'])),
            ];
        }

        return $data;
    }

    /**
     * Sanitize HTML content
     */
    private function sanitizeHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><em><u><ol><ul><li><a><blockquote>';
        return strip_tags($html, $allowedTags);
    }

    /**
     * Convert markdown to HTML
     */
    private function convertMarkdown(string $markdown): string
    {
        // Simple markdown conversion - in production, use a proper markdown parser
        $html = $markdown;
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $html);
        $html = nl2br($html);
        
        return $html;
    }

    /**
     * Generate content preview
     */
    private function generatePreview(string $content, int $length = 150): string
    {
        $stripped = strip_tags($content);
        return strlen($stripped) > $length ? substr($stripped, 0, $length) . '...' : $stripped;
    }

    /**
     * Create collection of resources with specific format
     */
    public static function collection($resource, string $format = 'json', array $includeRelations = [])
    {
        return parent::collection($resource)->additional([
            'meta' => [
                'format' => $format,
                'included' => $includeRelations,
                'timestamp' => now()->toISOString(),
            ]
        ]);
    }
}