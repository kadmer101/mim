<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\WebBloc;

class WebBlocRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $webBlocId = $this->route('webBloc')?->id ?? $this->route('web_bloc')?->id;

        return [
            'type' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('web_blocs', 'type')->ignore($webBlocId),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'version' => [
                'required',
                'string',
                'regex:/^\d+\.\d+\.\d+$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],
            'attributes' => [
                'nullable',
                'array',
            ],
            'attributes.*' => [
                'array',
            ],
            'attributes.*.type' => [
                'required_with:attributes.*',
                'string',
                'in:string,text,integer,float,boolean,array,date,datetime,email,url,json',
            ],
            'attributes.*.required' => [
                'boolean',
            ],
            'attributes.*.validation' => [
                'nullable',
                'string',
                'max:500',
            ],
            'attributes.*.default' => [
                'nullable',
            ],
            'attributes.*.description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'crud' => [
                'required',
                'array',
            ],
            'crud.create' => [
                'boolean',
            ],
            'crud.read' => [
                'boolean',
            ],
            'crud.update' => [
                'boolean',
            ],
            'crud.delete' => [
                'boolean',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
            'metadata.category' => [
                'nullable',
                'string',
                'in:content,social,ecommerce,analytics,utility,authentication,media,form',
            ],
            'metadata.tags' => [
                'nullable',
                'array',
            ],
            'metadata.tags.*' => [
                'string',
                'max:50',
            ],
            'metadata.author' => [
                'nullable',
                'string',
                'max:100',
            ],
            'metadata.license' => [
                'nullable',
                'string',
                'max:100',
            ],
            'metadata.documentation_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'metadata.demo_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'metadata.repository_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'component_code' => [
                'nullable',
                'string',
                'max:100000',
            ],
            'component_css' => [
                'nullable',
                'string',
                'max:50000',
            ],
            'component_js' => [
                'nullable',
                'string',
                'max:50000',
            ],
            'template_blade' => [
                'nullable',
                'string',
                'max:50000',
            ],
            'is_public' => [
                'boolean',
            ],
            'is_core' => [
                'boolean',
            ],
            'status' => [
                'required',
                'in:active,inactive,deprecated,beta',
            ],
            'min_laravel_version' => [
                'nullable',
                'string',
                'regex:/^\d+\.\d+$/',
            ],
            'min_php_version' => [
                'nullable',
                'string',
                'regex:/^\d+\.\d+$/',
            ],
            'dependencies' => [
                'nullable',
                'array',
            ],
            'dependencies.*' => [
                'string',
                'max:100',
            ],
            'compatibility' => [
                'nullable',
                'array',
            ],
            'compatibility.frameworks' => [
                'nullable',
                'array',
            ],
            'compatibility.frameworks.*' => [
                'string',
                'in:alpine,vue,react,jquery,vanilla',
            ],
            'compatibility.browsers' => [
                'nullable',
                'array',
            ],
            'compatibility.browsers.*' => [
                'string',
                'in:chrome,firefox,safari,edge,ie11',
            ],
            'default_config' => [
                'nullable',
                'array',
            ],
            'installation_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'changelog' => [
                'nullable',
                'array',
            ],
            'changelog.*' => [
                'array',
            ],
            'changelog.*.version' => [
                'required_with:changelog.*',
                'string',
            ],
            'changelog.*.date' => [
                'required_with:changelog.*',
                'date',
            ],
            'changelog.*.changes' => [
                'required_with:changelog.*',
                'array',
            ],
            'changelog.*.changes.*' => [
                'string',
                'max:500',
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
            'type.required' => 'WebBloc type is required.',
            'type.regex' => 'WebBloc type can only contain lowercase letters, numbers, hyphens, and underscores.',
            'type.unique' => 'A WebBloc with this type already exists.',
            'name.required' => 'WebBloc name is required.',
            'name.min' => 'WebBloc name must be at least 2 characters.',
            'version.required' => 'Version is required.',
            'version.regex' => 'Version must be in semantic versioning format (e.g., 1.0.0).',
            'attributes.*.type.required_with' => 'Attribute type is required when defining an attribute.',
            'attributes.*.type.in' => 'Invalid attribute type selected.',
            'crud.required' => 'CRUD operations configuration is required.',
            'metadata.category.in' => 'Invalid category selected.',
            'metadata.documentation_url.url' => 'Documentation URL must be a valid URL.',
            'metadata.demo_url.url' => 'Demo URL must be a valid URL.',
            'metadata.repository_url.url' => 'Repository URL must be a valid URL.',
            'component_code.max' => 'Component code cannot exceed 100,000 characters.',
            'component_css.max' => 'Component CSS cannot exceed 50,000 characters.',
            'component_js.max' => 'Component JavaScript cannot exceed 50,000 characters.',
            'template_blade.max' => 'Blade template cannot exceed 50,000 characters.',
            'status.required' => 'WebBloc status is required.',
            'status.in' => 'Invalid status selected.',
            'min_laravel_version.regex' => 'Laravel version must be in format X.Y (e.g., 8.0).',
            'min_php_version.regex' => 'PHP version must be in format X.Y (e.g., 8.0).',
            'compatibility.frameworks.*.in' => 'Invalid framework selected.',
            'compatibility.browsers.*.in' => 'Invalid browser selected.',
            'installation_notes.max' => 'Installation notes cannot exceed 5,000 characters.',
            'changelog.*.version.required_with' => 'Version is required for changelog entries.',
            'changelog.*.date.required_with' => 'Date is required for changelog entries.',
            'changelog.*.changes.required_with' => 'Changes are required for changelog entries.',
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
            'type' => 'WebBloc type',
            'name' => 'WebBloc name',
            'min_laravel_version' => 'minimum Laravel version',
            'min_php_version' => 'minimum PHP version',
            'is_public' => 'public availability',
            'is_core' => 'core WebBloc',
            'metadata.category' => 'category',
            'metadata.documentation_url' => 'documentation URL',
            'metadata.demo_url' => 'demo URL',
            'metadata.repository_url' => 'repository URL',
            'component_code' => 'component code',
            'component_css' => 'component CSS',
            'component_js' => 'component JavaScript',
            'template_blade' => 'Blade template',
            'installation_notes' => 'installation notes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize the type to lowercase with underscores
        if ($this->has('type')) {
            $type = trim($this->input('type'));
            $type = strtolower($type);
            $type = preg_replace('/[^a-z0-9_-]/', '_', $type);
            $type = preg_replace('/_+/', '_', $type);
            $type = trim($type, '_');
            $this->merge(['type' => $type]);
        }

        // Set default version if not provided
        if (!$this->has('version') || empty($this->input('version'))) {
            $this->merge(['version' => '1.0.0']);
        }

        // Ensure CRUD has all required keys
        $crud = $this->input('crud', []);
        $crud = array_merge([
            'create' => false,
            'read' => true,
            'update' => false,
            'delete' => false,
        ], $crud);
        $this->merge(['crud' => $crud]);

        // Process attributes
        $attributes = $this->input('attributes', []);
        if (is_array($attributes)) {
            foreach ($attributes as $key => &$attribute) {
                if (is_array($attribute)) {
                    $attribute = array_merge([
                        'type' => 'string',
                        'required' => false,
                        'validation' => null,
                        'default' => null,
                        'description' => null,
                    ], $attribute);
                }
            }
            $this->merge(['attributes' => $attributes]);
        }

        // Process metadata
        $metadata = $this->input('metadata', []);
        if (!isset($metadata['tags']) || !is_array($metadata['tags'])) {
            $metadata['tags'] = [];
        }
        $this->merge(['metadata' => $metadata]);

        // Convert boolean strings to actual booleans
        $booleanFields = [
            'is_public',
            'is_core',
            'crud.create',
            'crud.read',
            'crud.update',
            'crud.delete',
        ];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                if (is_string($value)) {
                    $this->merge([$field => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
                }
            }
        }

        // Process attributes boolean fields
        $attributes = $this->input('attributes', []);
        if (is_array($attributes)) {
            foreach ($attributes as $key => &$attribute) {
                if (isset($attribute['required']) && is_string($attribute['required'])) {
                    $attribute['required'] = filter_var($attribute['required'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
            }
            $this->merge(['attributes' => $attributes]);
        }
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Validate component code for security
        if ($this->has('component_code')) {
            $code = $this->input('component_code');
            $this->validatePhpCode($code);
        }

        // Validate JavaScript code
        if ($this->has('component_js')) {
            $js = $this->input('component_js');
            $this->validateJavaScriptCode($js);
        }

        // Validate CSS code
        if ($this->has('component_css')) {
            $css = $this->input('component_css');
            $this->validateCssCode($css);
        }

        // Validate Blade template
        if ($this->has('template_blade')) {
            $blade = $this->input('template_blade');
            $this->validateBladeTemplate($blade);
        }

        // Ensure at least one CRUD operation is enabled
        $crud = $this->input('crud', []);
        if (!$crud['create'] && !$crud['read'] && !$crud['update'] && !$crud['delete']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'crud' => ['At least one CRUD operation must be enabled.']
            ]);
        }

        // Auto-generate changelog entry if version changed
        $webBloc = $this->route('webBloc') ?? $this->route('web_bloc');
        if ($webBloc && $webBloc->version !== $this->input('version')) {
            $changelog = $this->input('changelog', []);
            
            // Add new changelog entry
            array_unshift($changelog, [
                'version' => $this->input('version'),
                'date' => now()->format('Y-m-d'),
                'changes' => ['Version updated via dashboard'],
            ]);
            
            $this->merge(['changelog' => $changelog]);
        }
    }

    /**
     * Validate PHP code for basic security issues
     */
    private function validatePhpCode(?string $code): void
    {
        if (empty($code)) return;

        // List of dangerous functions to check for
        $dangerousFunctions = [
            'eval', 'exec', 'system', 'shell_exec', 'passthru',
            'file_get_contents', 'file_put_contents', 'fopen', 'fwrite',
            'include', 'require', 'include_once', 'require_once',
            'unlink', 'rmdir', 'mysql_connect', 'pg_connect',
        ];

        foreach ($dangerousFunctions as $function) {
            if (strpos(strtolower($code), $function . '(') !== false) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'component_code' => ["Potentially dangerous function '{$function}' detected in code."]
                ]);
            }
        }

        // Check for PHP opening tags (shouldn't be in component code)
        if (strpos($code, '<?php') !== false || strpos($code, '<?=') !== false) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'component_code' => ['PHP opening tags are not allowed in component code.']
            ]);
        }
    }

    /**
     * Validate JavaScript code for basic security issues
     */
    private function validateJavaScriptCode(?string $code): void
    {
        if (empty($code)) return;

        // List of potentially dangerous patterns
        $dangerousPatterns = [
            'document.write',
            'eval(',
            'Function(',
            'setTimeout(',
            'setInterval(',
            'innerHTML\s*=',
            'outerHTML\s*=',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $code)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'component_js' => ["Potentially dangerous pattern '{$pattern}' detected in JavaScript code."]
                ]);
            }
        }
    }

    /**
     * Validate CSS code for basic security issues
     */
    private function validateCssCode(?string $code): void
    {
        if (empty($code)) return;

        // Check for JavaScript in CSS
        $dangerousPatterns = [
            'javascript:',
            'expression\s*\(',
            'behavior\s*:',
            'binding\s*:',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $code)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'component_css' => ["Potentially dangerous pattern '{$pattern}' detected in CSS code."]
                ]);
            }
        }
    }

    /**
     * Validate Blade template for basic security issues
     */
    private function validateBladeTemplate(?string $template): void
    {
        if (empty($template)) return;

        // Check for dangerous Blade directives
        $dangerousDirectives = [
            '@php',
            '@eval',
            '@include',
            '@extends',
        ];

        foreach ($dangerousDirectives as $directive) {
            if (strpos($template, $directive) !== false) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'template_blade' => ["Potentially dangerous Blade directive '{$directive}' detected."]
                ]);
            }
        }

        // Check for raw PHP code
        if (strpos($template, '<?php') !== false || strpos($template, '<?=') !== false) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'template_blade' => ['Raw PHP code is not allowed in Blade templates.']
            ]);
        }

        // Check for unescaped output that might be dangerous
        if (preg_match('/\{\!\!\s*\$.*?\!\!\}/', $template)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'template_blade' => ['Unescaped output detected. Use escaped output {{}} instead of {!! !!} for security.']
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic can go here
            
            // Validate version format more strictly
            if ($this->has('version')) {
                $version = $this->input('version');
                $parts = explode('.', $version);
                
                if (count($parts) !== 3) {
                    $validator->errors()->add('version', 'Version must have exactly 3 parts (major.minor.patch).');
                } else {
                    foreach ($parts as $part) {
                        if (!is_numeric($part) || $part < 0) {
                            $validator->errors()->add('version', 'Version parts must be non-negative integers.');
                            break;
                        }
                    }
                }
            }

            // Validate attribute names
            if ($this->has('attributes')) {
                $attributes = $this->input('attributes');
                foreach ($attributes as $attributeName => $attributeConfig) {
                    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $attributeName)) {
                        $validator->errors()->add(
                            "attributes.{$attributeName}",
                            "Attribute name '{$attributeName}' must be a valid identifier."
                        );
                    }
                }
            }
        });
    }
}