<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\WebBloc;
use App\Services\WebBlocService;

class WebBlocRequest extends FormRequest
{
    protected $webBlocService;

    public function __construct(WebBlocService $webBlocService)
    {
        parent::__construct();
        $this->webBlocService = $webBlocService;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $type = $this->route('type');
        $method = $this->method();
        
        // Get base rules for the WebBloc type
        $baseRules = $this->getBaseRules();
        
        // Get type-specific rules
        $typeRules = $this->getTypeSpecificRules($type);
        
        // Get method-specific rules
        $methodRules = $this->getMethodSpecificRules($method);
        
        return array_merge($baseRules, $typeRules, $methodRules);
    }

    /**
     * Get base validation rules for all WebBlocs
     */
    private function getBaseRules(): array
    {
        return [
            'page_url' => 'required|string|max:500',
            'data' => 'required|array',
            'metadata' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,pending,approved,rejected',
            'parent_id' => 'nullable|integer|exists:web_blocs,id',
            'sort_order' => 'nullable|integer|min:0'
        ];
    }

    /**
     * Get validation rules specific to WebBloc type
     */
    private function getTypeSpecificRules(string $type): array
    {
        $typeDefinition = $this->webBlocService->getTypeMetadata($type);
        
        if (!$typeDefinition) {
            return [];
        }

        $rules = [];
        $attributes = $typeDefinition['attributes'] ?? [];

        foreach ($attributes as $attribute => $config) {
            $fieldRules = $this->buildFieldRules($attribute, $config);
            if ($fieldRules) {
                $rules["data.{$attribute}"] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Build field validation rules from attribute configuration
     */
    private function buildFieldRules(string $attribute, array $config): array
    {
        $rules = [];

        // Required validation
        if ($config['required'] ?? false) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type validation
        $type = $config['type'] ?? 'string';
        switch ($type) {
            case 'string':
                $rules[] = 'string';
                if (isset($config['max_length'])) {
                    $rules[] = "max:{$config['max_length']}";
                }
                if (isset($config['min_length'])) {
                    $rules[] = "min:{$config['min_length']}";
                }
                break;

            case 'integer':
                $rules[] = 'integer';
                if (isset($config['min'])) {
                    $rules[] = "min:{$config['min']}";
                }
                if (isset($config['max'])) {
                    $rules[] = "max:{$config['max']}";
                }
                break;

            case 'email':
                $rules[] = 'email:rfc';
                break;

            case 'url':
                $rules[] = 'url';
                break;

            case 'array':
                $rules[] = 'array';
                break;

            case 'boolean':
                $rules[] = 'boolean';
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'file':
                $rules[] = 'file';
                if (isset($config['mimes'])) {
                    $rules[] = "mimes:{$config['mimes']}";
                }
                if (isset($config['max_size'])) {
                    $rules[] = "max:{$config['max_size']}";
                }
                break;

            case 'enum':
                if (isset($config['options'])) {
                    $options = implode(',', $config['options']);
                    $rules[] = "in:{$options}";
                }
                break;
        }

        // Custom validation rules
        if (isset($config['validation'])) {
            if (is_array($config['validation'])) {
                $rules = array_merge($rules, $config['validation']);
            } else {
                $rules[] = $config['validation'];
            }
        }

        return $rules;
    }

    /**
     * Get method-specific validation rules
     */
    private function getMethodSpecificRules(string $method): array
    {
        switch (strtoupper($method)) {
            case 'POST':
                return [
                    'data' => 'required|array',
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'data' => 'sometimes|array',
                ];

            default:
                return [];
        }
    }

    /**
     * Get custom validation rules based on WebBloc type
     */
    private function getCustomRules(string $type): array
    {
        $customRules = [
            'comment' => [
                'data.content' => 'required|string|max:2000',
                'data.author_name' => 'required_without:user_id|string|max:100',
                'data.author_email' => 'required_without:user_id|email',
                'data.rating' => 'nullable|integer|between:1,5'
            ],

            'review' => [
                'data.title' => 'required|string|max:200',
                'data.content' => 'required|string|max:5000',
                'data.rating' => 'required|integer|between:1,5',
                'data.author_name' => 'required_without:user_id|string|max:100',
                'data.author_email' => 'required_without:user_id|email',
                'data.verified_purchase' => 'nullable|boolean'
            ],

            'testimonial' => [
                'data.content' => 'required|string|max:1000',
                'data.author_name' => 'required|string|max:100',
                'data.author_title' => 'nullable|string|max:200',
                'data.author_company' => 'nullable|string|max:200',
                'data.author_image' => 'nullable|url',
                'data.rating' => 'nullable|integer|between:1,5'
            ],

            'reaction' => [
                'data.type' => 'required|string|in:like,love,laugh,angry,sad,wow',
                'data.target_id' => 'required|integer',
                'data.target_type' => 'required|string'
            ],

            'form_submission' => [
                'data.form_fields' => 'required|array',
                'data.form_name' => 'required|string|max:100'
            ]
        ];

        return $customRules[$type] ?? [];
    }

    /**
     * Get custom attributes for error messages
     */
    public function attributes(): array
    {
        return [
            'data.content' => 'content',
            'data.title' => 'title',
            'data.rating' => 'rating',
            'data.author_name' => 'author name',
            'data.author_email' => 'author email',
            'page_url' => 'page URL'
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'data.content.required' => 'Content is required',
            'data.content.max' => 'Content cannot exceed :max characters',
            'data.rating.between' => 'Rating must be between 1 and 5',
            'data.author_email.email' => 'Please provide a valid email address',
            'page_url.required' => 'Page URL is required',
            'page_url.max' => 'Page URL cannot exceed 500 characters'
        ];
    }

    /**
     * Handle a failed validation attempt
     */
    protected function failedValidation(Validator $validator)
    {
        $responseFormat = $this->determineResponseFormat();
        
        if ($responseFormat === 'html') {
            $response = response()->view('webbloc.validation-error', [
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ])->setStatusCode(422);
        } else {
            $response = response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        throw new HttpResponseException($response);
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Sanitize and prepare data
        $data = $this->input('data', []);
        
        // Remove any XSS attempts
        $sanitizedData = $this->sanitizeData($data);
        
        // Merge sanitized data back
        $this->merge(['data' => $sanitizedData]);
    }

    /**
     * Sanitize input data
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous HTML tags
                $sanitized[$key] = strip_tags($value, '<p><br><strong><em><u><ol><ul><li><a>');
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Determine response format based on WebBloc configuration
     */
    private function determineResponseFormat(): string
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