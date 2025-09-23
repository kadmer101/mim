<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class WebsiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $websiteId = $this->route('website')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'url' => [
                'required',
                'url',
                'max:500',
                Rule::unique('websites', 'url')->ignore($websiteId),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'owner_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    // Only admins can set owner_id, and only if provided
                    if ($value && !Auth::user()->hasRole('admin')) {
                        $fail('You are not authorized to assign website ownership.');
                    }
                },
            ],
            'status' => [
                'required',
                'in:active,inactive,suspended,pending',
            ],
            'subscription_status' => [
                'required',
                'in:active,expired,cancelled,trial',
            ],
            'subscription_expires_at' => [
                'nullable',
                'date',
                'after:today',
            ],
            'allowed_domains' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $domains = array_filter(array_map('trim', explode("\n", $value)));
                        foreach ($domains as $domain) {
                            if (!$this->isValidDomain($domain)) {
                                $fail("Invalid domain format: {$domain}");
                            }
                        }
                    }
                },
            ],
            'cors_origins' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $origins = array_filter(array_map('trim', explode("\n", $value)));
                        foreach ($origins as $origin) {
                            if ($origin !== '*' && !filter_var($origin, FILTER_VALIDATE_URL)) {
                                $fail("Invalid CORS origin: {$origin}");
                            }
                        }
                    }
                },
            ],
            'rate_limit_per_minute' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
            'rate_limit_per_hour' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
            'rate_limit_per_day' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000000',
            ],
            'cdn_enabled' => [
                'boolean',
            ],
            'cdn_cache_ttl' => [
                'nullable',
                'integer',
                'min:60',
                'max:86400',
            ],
            'notification_webhook' => [
                'nullable',
                'url',
                'max:500',
            ],
            'settings' => [
                'nullable',
                'array',
            ],
            'settings.analytics_enabled' => [
                'boolean',
            ],
            'settings.error_reporting_enabled' => [
                'boolean',
            ],
            'settings.maintenance_mode' => [
                'boolean',
            ],
            'settings.debug_mode' => [
                'boolean',
            ],
            'settings.custom_css' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'settings.custom_js' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'settings.timezone' => [
                'nullable',
                'string',
                'in:' . implode(',', timezone_identifiers_list()),
            ],
            'settings.date_format' => [
                'nullable',
                'string',
                'in:Y-m-d,d/m/Y,m/d/Y,Y-m-d H:i:s,d/m/Y H:i:s,m/d/Y H:i:s',
            ],
            'webbloc_settings' => [
                'nullable',
                'array',
            ],
            'webbloc_settings.enabled_types' => [
                'nullable',
                'array',
            ],
            'webbloc_settings.enabled_types.*' => [
                'string',
                'exists:web_blocs,type',
            ],
            'webbloc_settings.default_theme' => [
                'nullable',
                'string',
                'in:default,minimal,modern,classic',
            ],
            'webbloc_settings.require_authentication' => [
                'boolean',
            ],
            'webbloc_settings.allow_anonymous_comments' => [
                'boolean',
            ],
            'webbloc_settings.moderation_enabled' => [
                'boolean',
            ],
            'webbloc_settings.spam_protection' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Website name is required.',
            'name.min' => 'Website name must be at least 2 characters.',
            'name.max' => 'Website name cannot exceed 255 characters.',
            'url.required' => 'Website URL is required.',
            'url.url' => 'Please enter a valid URL (including http:// or https://).',
            'url.unique' => 'This URL is already registered with another website.',
            'owner_id.exists' => 'The selected owner does not exist.',
            'status.required' => 'Website status is required.',
            'status.in' => 'Invalid website status selected.',
            'subscription_status.required' => 'Subscription status is required.',
            'subscription_status.in' => 'Invalid subscription status selected.',
            'subscription_expires_at.date' => 'Please enter a valid expiration date.',
            'subscription_expires_at.after' => 'Expiration date must be in the future.',
            'rate_limit_per_minute.min' => 'Rate limit per minute must be at least 1.',
            'rate_limit_per_minute.max' => 'Rate limit per minute cannot exceed 10,000.',
            'rate_limit_per_hour.min' => 'Rate limit per hour must be at least 1.',
            'rate_limit_per_hour.max' => 'Rate limit per hour cannot exceed 100,000.',
            'rate_limit_per_day.min' => 'Rate limit per day must be at least 1.',
            'rate_limit_per_day.max' => 'Rate limit per day cannot exceed 1,000,000.',
            'notification_webhook.url' => 'Please enter a valid webhook URL.',
            'cdn_cache_ttl.min' => 'CDN cache TTL must be at least 60 seconds.',
            'cdn_cache_ttl.max' => 'CDN cache TTL cannot exceed 86400 seconds (24 hours).',
            'settings.custom_css.max' => 'Custom CSS cannot exceed 10,000 characters.',
            'settings.custom_js.max' => 'Custom JavaScript cannot exceed 10,000 characters.',
            'settings.timezone.in' => 'Please select a valid timezone.',
            'webbloc_settings.enabled_types.*.exists' => 'One or more selected WebBloc types do not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'website name',
            'url' => 'website URL',
            'owner_id' => 'website owner',
            'subscription_expires_at' => 'subscription expiration date',
            'rate_limit_per_minute' => 'rate limit per minute',
            'rate_limit_per_hour' => 'rate limit per hour',
            'rate_limit_per_day' => 'rate limit per day',
            'cdn_cache_ttl' => 'CDN cache TTL',
            'notification_webhook' => 'notification webhook URL',
            'settings.analytics_enabled' => 'analytics',
            'settings.error_reporting_enabled' => 'error reporting',
            'settings.maintenance_mode' => 'maintenance mode',
            'settings.debug_mode' => 'debug mode',
            'settings.custom_css' => 'custom CSS',
            'settings.custom_js' => 'custom JavaScript',
            'settings.timezone' => 'timezone',
            'settings.date_format' => 'date format',
            'webbloc_settings.enabled_types' => 'enabled WebBloc types',
            'webbloc_settings.default_theme' => 'default theme',
            'webbloc_settings.require_authentication' => 'require authentication',
            'webbloc_settings.allow_anonymous_comments' => 'allow anonymous comments',
            'webbloc_settings.moderation_enabled' => 'moderation',
            'webbloc_settings.spam_protection' => 'spam protection',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and normalize the URL
        if ($this->has('url')) {
            $url = trim($this->input('url'));
            
            // Add protocol if missing
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }
            
            // Remove trailing slash
            $url = rtrim($url, '/');
            
            $this->merge(['url' => $url]);
        }

        // Process allowed domains
        if ($this->has('allowed_domains')) {
            $domains = $this->input('allowed_domains');
            if (is_string($domains)) {
                // Clean up the domains list
                $domains = array_filter(array_map('trim', explode("\n", $domains)));
                $this->merge(['allowed_domains' => implode("\n", $domains)]);
            }
        }

        // Process CORS origins
        if ($this->has('cors_origins')) {
            $origins = $this->input('cors_origins');
            if (is_string($origins)) {
                // Clean up the origins list
                $origins = array_filter(array_map('trim', explode("\n", $origins)));
                $this->merge(['cors_origins' => implode("\n", $origins)]);
            }
        }

        // Set default values for settings
        $settings = $this->input('settings', []);
        $settings = array_merge([
            'analytics_enabled' => true,
            'error_reporting_enabled' => true,
            'maintenance_mode' => false,
            'debug_mode' => false,
            'timezone' => config('app.timezone'),
            'date_format' => 'Y-m-d H:i:s',
        ], $settings);
        $this->merge(['settings' => $settings]);

        // Set default values for webbloc_settings
        $webBlocSettings = $this->input('webbloc_settings', []);
        $webBlocSettings = array_merge([
            'enabled_types' => [],
            'default_theme' => 'default',
            'require_authentication' => false,
            'allow_anonymous_comments' => true,
            'moderation_enabled' => false,
            'spam_protection' => true,
        ], $webBlocSettings);
        $this->merge(['webbloc_settings' => $webBlocSettings]);

        // Convert boolean strings to actual booleans
        $booleanFields = [
            'cdn_enabled',
            'settings.analytics_enabled',
            'settings.error_reporting_enabled',
            'settings.maintenance_mode',
            'settings.debug_mode',
            'webbloc_settings.require_authentication',
            'webbloc_settings.allow_anonymous_comments',
            'webbloc_settings.moderation_enabled',
            'webbloc_settings.spam_protection',
        ];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                $this->merge([$field => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
            }
        }
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Additional processing after validation passes
        
        // Ensure rate limits are in ascending order
        $perMinute = $this->input('rate_limit_per_minute', 60);
        $perHour = $this->input('rate_limit_per_hour', 1000);
        $perDay = $this->input('rate_limit_per_day', 10000);

        // Auto-adjust if they don't make sense
        if ($perHour < $perMinute * 60) {
            $this->merge(['rate_limit_per_hour' => $perMinute * 60]);
        }
        
        if ($perDay < $perHour * 24) {
            $this->merge(['rate_limit_per_day' => $perHour * 24]);
        }

        // Process custom CSS and JS for security
        if ($this->has('settings.custom_css')) {
            $css = $this->input('settings.custom_css');
            // Basic XSS protection for CSS
            $css = preg_replace('/javascript:/i', '', $css);
            $css = preg_replace('/expression\s*\(/i', '', $css);
            $settings = $this->input('settings');
            $settings['custom_css'] = $css;
            $this->merge(['settings' => $settings]);
        }

        if ($this->has('settings.custom_js')) {
            $js = $this->input('settings.custom_js');
            // Basic validation for JS (you might want more sophisticated validation)
            if (strpos($js, '<script') !== false || strpos($js, '</script>') !== false) {
                $js = strip_tags($js);
            }
            $settings = $this->input('settings');
            $settings['custom_js'] = $js;
            $this->merge(['settings' => $settings]);
        }
    }

    /**
     * Validate domain format
     */
    private function isValidDomain(string $domain): bool
    {
        // Allow wildcard domains
        if (str_starts_with($domain, '*.')) {
            $domain = substr($domain, 2);
        }
        
        // Basic domain validation
        return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }
}