<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WebBloc extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'version',
        'description',
        'attributes',
        'default_attributes',
        'crud',
        'metadata',
        'blade_component',
        'alpine_component',
        'css_styles',
        'dependencies',
        'api_endpoints',
        'permissions',
        'integration_syntax',
        'configuration_schema',
        'validation_rules',
        'display_options',
        'author',
        'author_email',
        'license',
        'repository_url',
        'documentation_url',
        'status',
        'is_core',
        'is_public',
        'published_at',
        'min_php_version',
        'min_laravel_version',
        'required_packages',
        'installation_count',
        'usage_count',
        'average_rating',
        'review_count',
        'icon_path',
        'screenshots',
        'assets',
        'supported_locales',
        'translations',
        'security_features',
        'sanitize_input',
        'validate_permissions',
        'cacheable',
        'cache_ttl',
        'lazy_load',
        'category',
        'tags',
        'installation_instructions',
        'setup_commands',
        'migration_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attributes' => 'array',
        'default_attributes' => 'array',
        'crud' => 'array',
        'metadata' => 'array',
        'dependencies' => 'array',
        'api_endpoints' => 'array',
        'permissions' => 'array',
        'configuration_schema' => 'array',
        'validation_rules' => 'array',
        'display_options' => 'array',
        'published_at' => 'datetime',
        'required_packages' => 'array',
        'installation_count' => 'integer',
        'usage_count' => 'integer',
        'average_rating' => 'float',
        'review_count' => 'integer',
        'screenshots' => 'array',
        'assets' => 'array',
        'supported_locales' => 'array',
        'translations' => 'array',
        'security_features' => 'array',
        'sanitize_input' => 'boolean',
        'validate_permissions' => 'boolean',
        'cacheable' => 'boolean',
        'cache_ttl' => 'integer',
        'lazy_load' => 'boolean',
        'tags' => 'array',
        'setup_commands' => 'array',
        'is_core' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * The supported WebBloc types.
     *
     * @var array<string, string>
     */
    public const SUPPORTED_TYPES = [
        'auth' => 'Authentication System',
        'comment' => 'Comments System',
        'review' => 'Reviews System',
        'testimonial' => 'Testimonials',
        'reaction' => 'Reactions',
        'social_share' => 'Social Sharing',
        'profile' => 'User Profiles',
        'notification' => 'Notifications',
        'contact' => 'Contact Forms',
        'newsletter' => 'Newsletter Signup',
        'poll' => 'Polls and Surveys',
        'chat' => 'Live Chat',
    ];

    /**
     * The CRUD operations.
     *
     * @var array<string>
     */
    public const CRUD_OPERATIONS = ['create', 'read', 'update', 'delete'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($webBloc) {
            if (!$webBloc->crud) {
                $webBloc->crud = array_fill_keys(self::CRUD_OPERATIONS, true);
            }
            
            if (!$webBloc->attributes) {
                $webBloc->attributes = config('webbloc.webbloc_standards.default_attributes');
            }
        });

        static::updated(function ($webBloc) {
            // Clear cache when WebBloc is updated
            Cache::tags(['webbloc', 'webbloc_' . $webBloc->type])->flush();
        });
    }

    /**
     * Get the instances of this WebBloc.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WebBlocInstance::class);
    }

    /**
     * Check if this WebBloc supports a specific CRUD operation.
     */
    public function supportsCrudOperation(string $operation): bool
    {
        $crud = $this->crud ?? [];
        return $crud[$operation] ?? false;
    }

    /**
     * Get the default attributes merged with custom attributes.
     */
    public function getMergedAttributes(array $customAttributes = []): array
    {
        $defaultAttributes = $this->default_attributes ?? [];
        $webBlocAttributes = $this->attributes ?? [];
        
        return array_merge($defaultAttributes, $webBlocAttributes, $customAttributes);
    }

    /**
     * Generate the integration code for this WebBloc.
     */
    public function generateIntegrationCode(array $attributes = []): string
    {
        $tagPrefix = config('webbloc.integration.tag_prefix');
        $attributePrefix = config('webbloc.integration.attribute_prefix');
        $loadingText = config('webbloc.integration.loading_text');
        
        $attributesJson = !empty($attributes) ? json_encode($attributes) : '';
        $attributeString = $attributesJson ? " {$attributePrefix}='{$attributesJson}'" : '';
        
        return "<div {$tagPrefix}=\"{$this->type}\"{$attributeString}>{$loadingText}</div>";
    }

    /**
     * Get the API endpoints for this WebBloc.
     */
    public function getApiEndpoints(): array
    {
        $baseEndpoints = [
            'list' => "GET /api/webblocs/{$this->type}",
            'show' => "GET /api/webblocs/{$this->type}/{id}",
        ];
        
        $crud = $this->crud ?? [];
        
        if ($crud['create'] ?? false) {
            $baseEndpoints['create'] = "POST /api/webblocs/{$this->type}";
        }
        
        if ($crud['update'] ?? false) {
            $baseEndpoints['update'] = "PUT /api/webblocs/{$this->type}/{id}";
        }
        
        if ($crud['delete'] ?? false) {
            $baseEndpoints['delete'] = "DELETE /api/webblocs/{$this->type}/{id}";
        }
        
        return array_merge($baseEndpoints, $this->api_endpoints ?? []);
    }

    /**
     * Get the validation rules for this WebBloc.
     */
    public function getValidationRules(): array
    {
        $baseRules = [
            'page_url' => 'required|string|max:500',
            'data' => 'required|array',
        ];
        
        return array_merge($baseRules, $this->validation_rules ?? []);
    }

    /**
     * Render the WebBloc component.
     */
    public function render(array $data = [], array $attributes = []): string
    {
        $mergedAttributes = $this->getMergedAttributes($attributes);
        
        // If blade component exists, render it
        if ($this->blade_component) {
            return view()->make('webblocs.' . $this->type, [
                'webbloc' => $this,
                'data' => $data,
                'attributes' => $mergedAttributes,
            ])->render();
        }
        
        // Fallback to basic HTML structure
        return $this->renderFallbackHtml($data, $mergedAttributes);
    }

    /**
     * Render fallback HTML when no Blade component is available.
     */
    protected function renderFallbackHtml(array $data, array $attributes): string
    {
        $html = "<div class=\"webbloc webbloc-{$this->type}\" data-webbloc-type=\"{$this->type}\">";
        
        if (!empty($data)) {
            foreach ($data as $item) {
                $html .= "<div class=\"webbloc-item\">";
                $html .= $this->renderDataItem($item);
                $html .= "</div>";
            }
        } else {
            $html .= "<div class=\"webbloc-empty\">No {$this->name} available</div>";
        }
        
        $html .= "</div>";
        
        return $html;
    }

    /**
     * Render a single data item.
     */
    protected function renderDataItem(array $item): string
    {
        $html = '';
        
        // Basic rendering based on WebBloc type
        switch ($this->type) {
            case 'comment':
                $html .= "<div class=\"comment\">";
                $html .= "<div class=\"comment-content\">" . ($item['content'] ?? '') . "</div>";
                $html .= "<div class=\"comment-meta\">";
                $html .= "<span class=\"author\">" . ($item['author'] ?? 'Anonymous') . "</span>";
                $html .= "<span class=\"date\">" . ($item['created_at'] ?? '') . "</span>";
                $html .= "</div>";
                $html .= "</div>";
                break;
                
            case 'review':
                $html .= "<div class=\"review\">";
                if (isset($item['rating'])) {
                    $html .= "<div class=\"rating\">";
                    for ($i = 1; $i <= 5; $i++) {
                        $class = $i <= $item['rating'] ? 'star filled' : 'star';
                        $html .= "<span class=\"{$class}\">★</span>";
                    }
                    $html .= "</div>";
                }
                $html .= "<div class=\"review-content\">" . ($item['content'] ?? '') . "</div>";
                $html .= "<div class=\"review-meta\">";
                $html .= "<span class=\"author\">" . ($item['author'] ?? 'Anonymous') . "</span>";
                $html .= "<span class=\"date\">" . ($item['created_at'] ?? '') . "</span>";
                $html .= "</div>";
                $html .= "</div>";
                break;
                
            default:
                // Generic rendering
                $html .= "<div class=\"item\">";
                foreach ($item as $key => $value) {
                    if (is_string($value) || is_numeric($value)) {
                        $html .= "<div class=\"{$key}\">{$value}</div>";
                    }
                }
                $html .= "</div>";
                break;
        }
        
        return $html;
    }

    /**
     * Get the CSS styles for this WebBloc.
     */
    public function getCssStyles(): string
    {
        if ($this->css_styles) {
            return $this->css_styles;
        }
        
        // Return default styles based on type
        return $this->getDefaultCssStyles();
    }

    /**
     * Get default CSS styles for the WebBloc type.
     */
    protected function getDefaultCssStyles(): string
    {
        return "
        .webbloc-{$this->type} {
            margin: 1rem 0;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            background: #ffffff;
        }
        
        .webbloc-{$this->type} .webbloc-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .webbloc-{$this->type} .webbloc-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .webbloc-{$this->type} .webbloc-empty {
            text-align: center;
            color: #64748b;
            font-style: italic;
        }
        ";
    }

    /**
     * Increment the installation count.
     */
    public function incrementInstallationCount(): void
    {
        $this->increment('installation_count');
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsageCount(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Check if this WebBloc is compatible with the current system.
     */
    public function isCompatible(): bool
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, $this->min_php_version, '<')) {
            return false;
        }
        
        // Check Laravel version
        $laravelVersion = app()->version();
        if (version_compare($laravelVersion, $this->min_laravel_version, '<')) {
            return false;
        }
        
        return true;
    }

    /**
     * Get cached WebBloc data.
     */
    public static function getCached(string $type): ?self
    {
        $cacheKey = "webbloc_{$type}";
        
        return Cache::tags(['webbloc', "webbloc_{$type}"])
            ->remember($cacheKey, 3600, function () use ($type) {
                return static::where('type', $type)
                    ->where('status', 'active')
                    ->first();
            });
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by public WebBlocs.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter by core WebBlocs.
     */
    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    /**
     * Scope to search WebBlocs.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('type', 'like', "%{$term}%")
              ->orJsonContains('tags', $term);
        });
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'draft' => 'yellow',
            'deprecated' => 'orange',
            'disabled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the formatted installation count.
     */
    public function getFormattedInstallationCountAttribute(): string
    {
        if ($this->installation_count >= 1000000) {
            return number_format($this->installation_count / 1000000, 1) . 'M';
        } elseif ($this->installation_count >= 1000) {
            return number_format($this->installation_count / 1000, 1) . 'K';
        }
        
        return number_format($this->installation_count);
    }
}