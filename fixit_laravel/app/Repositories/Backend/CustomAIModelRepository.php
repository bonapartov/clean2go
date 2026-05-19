<?php

namespace App\Repositories\Backend;

use Exception;
use GuzzleHttp\Client;
use App\Models\CustomAIModel;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use App\Http\Traits\AIContentGenerationTrait;
use GuzzleHttp\Exception\RequestException;
use Prettus\Repository\Eloquent\BaseRepository;

class CustomAIModelRepository extends BaseRepository
{
    use AIContentGenerationTrait; 

    public function model()
    {
        return CustomAIModel::class;
    }

    public function index($dataTable)
    {
        return $dataTable->render('backend.custom-ai-model.index');
    }

    public function create($attributes = [])
    {
        return view('backend.custom-ai-model.create');
    }

    public function edit($id)
    {
        $model = $this->model->findOrFail($id);
        return view('backend.custom-ai-model.edit', [
            'model' => $model
        ]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            // If this is set as default, unset all other defaults
            if (isset($request['is_default']) && $request['is_default'] == '1') {
                $this->model->where('is_default', true)->update(['is_default' => false]);
                $request['is_default'] = true;
            } else {
                $request['is_default'] = false;
            }

            // Extract payload
            if (isset($request['payload'])) {
                $decodedPayload = json_decode($request['payload'], true);
                if (!empty($decodedPayload) && is_array($decodedPayload)) {
                    $request['payload'] = $decodedPayload;
                } else {
                    $request['payload'] = null;
                }
            } elseif (isset($request['payload_key']) && isset($request['payload_value'])) {
                $payload = $this->extractKeyValuePairs($request, 'payload_key', 'payload_value');
                $request['payload'] = $payload;
            }

            // Extract parameters
            if (isset($request['param_key']) && isset($request['param_value'])) {
                $param = $this->extractKeyValuePairs($request, 'param_key', 'param_value');
                $request['params'] = $param;
            }

            // Extract headers
            if (isset($request['header_key']) && isset($request['header_value'])) {
                $header = $this->extractKeyValuePairs($request, 'header_key', 'header_value');
                $request['headers'] = $header;
            }

            $model = $this->model->create($request);

            DB::commit();
            return to_route('backend.custom-ai-model.index')->with('message', __('static.custom_ai_models.create_successfully'));

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $model = $this->model->findOrFail($id);
           
            // If this is set as default, unset all other defaults
            if (isset($request['is_default']) == '1') {
                $this->model->where('id', '!=', $id)->where('is_default', true)->update(['is_default' => false]);
            } 
            // Extract payload
            if (isset($request['payload'])) {
                $decodedPayload = json_decode($request['payload'], true);
                if (!empty($decodedPayload) && is_array($decodedPayload)) {
                    $request['payload'] = $decodedPayload;
                } else {
                    $request['payload'] = null;
                }
            } elseif (isset($request['payload_key']) && isset($request['payload_value'])) {
                $payload = $this->extractKeyValuePairs($request, 'payload_key', 'payload_value');
                $request['payload'] = $payload;
            }

            // Extract parameters
            if (isset($request['param_key']) && isset($request['param_value'])) {
                $param = $this->extractKeyValuePairs($request, 'param_key', 'param_value');
                $request['params'] = $param;
            }

            // Extract headers
            if (isset($request['header_key']) && isset($request['header_value'])) {
                $header = $this->extractKeyValuePairs($request, 'header_key', 'header_value');
                $request['headers'] = $header;
            }

            $model->update($request);

            DB::commit();
            return to_route('backend.custom-ai-model.index')->with('message', __('static.custom_ai_models.update_successfully'));

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $model = $this->model->findOrFail($id);
            
            // Don't allow deleting the default model if it's the only one
            if ($model->is_default && $this->model->count() === 1) {
                throw new Exception("Cannot delete the only model. Please create another model first.", 400);
            }
            
            if($model->is_default == 1){
                throw new Exception("Default model cannot be deleted.");
            }
            
            $model->delete();

            DB::commit();
            return to_route('backend.custom-ai-model.index')->with('message', __('static.custom_ai_models.delete_successfully'));

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function setDefault($id)
    {
        DB::beginTransaction();
        try {
            // Unset all other defaults
            $this->model->where('is_default', true)->update(['is_default' => false]);
            
            // Set this one as default
            $model = $this->model->findOrFail($id);
            $model->update(['is_default' => true]);

            DB::commit();
            return to_route('backend.custom-ai-model.index')->with('success', __('static.custom_ai_models.set_default_successfully'));

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function extractKeyValuePairs($request, $keyField, $valueField)
    {
        $result = [];
        if (isset($request[$keyField]) && isset($request[$valueField])) {
            foreach ($request[$keyField] as $index => $key) {
                if (!empty($key)) {
                    $result[$key] = $request[$valueField][$index] ?? '';
                }
            }
        }
        return $result;
    }

    public function testCreate($testPrompt, $formData)
    {
        try {
            // Validate required fields
            if (empty($formData['provider'])) {
                throw new Exception("Provider is required to test the model.");
            }
            
            if (empty($formData['api_key'])) {
                throw new Exception("API Key is required to test the model.");
            }
            
            // Create a temporary model object from form data
            $tempModel = new CustomAIModel();
            $tempModel->api_key = $formData['api_key'] ?? '';
            $tempModel->base_url = $formData['base_url'] ?? null;
            $tempModel->model_name = $formData['model_name'] ?? null;
            $tempModel->provider = $formData['provider'];
            
            // Prepare headers from form
            if (isset($formData['header_key']) && isset($formData['header_value'])) {
                $headers = $this->extractKeyValuePairs($formData, 'header_key', 'header_value');
            } else {
                $headers = [];
            }
            $headers = $this->replacePlaceholders($headers, $tempModel);
            
            // Add default headers if empty
            if (empty($headers)) {
                $headers = [
                    'Content-Type' => 'application/json',
                ];
                
                // Add Authorization header based on provider
                if ($tempModel->provider === 'openai' || $tempModel->provider === 'anthropic') {
                    $headers['Authorization'] = 'Bearer ' . $tempModel->api_key;
                } elseif ($tempModel->provider === 'google') {
                    // Google Gemini uses API key in URL params or header
                    $headers['x-goog-api-key'] = $tempModel->api_key;
                } else {
                    $headers['Authorization'] = 'Bearer ' . $tempModel->api_key;
                }
            }
            
            // Prepare params from form
            if (isset($formData['param_key']) && isset($formData['param_value'])) {
                $params = $this->extractKeyValuePairs($formData, 'param_key', 'param_value');
            } else {
                $params = [];
            }
            $params = $this->replacePlaceholders($params, $tempModel);
            
            // Prepare payload from form
            if (isset($formData['payload'])) {
                $decodedPayload = json_decode($formData['payload'], true);
                if (!empty($decodedPayload) && is_array($decodedPayload)) {
                    $payload = $decodedPayload;
                } else {
                    $payload = [];
                }
            } elseif (isset($formData['payload_key']) && isset($formData['payload_value'])) {
                $payload = $this->extractKeyValuePairs($formData, 'payload_key', 'payload_value');
            } else {
                $payload = [];
            }
            
            // If payload is empty, create default payload based on provider
            if (empty($payload)) {
                $payload = $this->getDefaultPayload($tempModel, $testPrompt);
            } else {
                $payload = $this->replacePlaceholders($payload, $tempModel);
                // Replace prompt placeholder in payload
                $payload = $this->replacePromptInPayload($payload, $testPrompt);
            }
            
            // Determine endpoint based on provider
            $endpoint = $this->getEndpoint($tempModel);
            
            // Prepare full URL
            if ($tempModel->base_url) {
                // If base_url is provided, use it and append endpoint
                $baseUrlClean = rtrim($tempModel->base_url, '/');
                // Check if base_url already includes /v1
                $hasV1 = (substr($baseUrlClean, -3) === '/v1');
                // If endpoint starts with /v1 and base_url already has /v1, remove /v1 from endpoint
                if ($hasV1 && strpos($endpoint, '/v1/') === 0) {
                    $endpoint = substr($endpoint, 3); // Remove /v1 from start
                }
                $url = $baseUrlClean . $endpoint;
            } else {
                // Use default URL for provider
                $url = $this->getDefaultUrl($tempModel) . $endpoint;
            }
            
            // Make HTTP request
            $client = new Client();
            
            $requestOptions = [
                'headers' => $headers,
                'timeout' => 30,
            ];
            
            // Add params to URL if GET, or to body if POST
            if (strtoupper($this->getMethod($tempModel)) === 'GET') {
                if (!empty($params)) {
                    $url .= '?' . http_build_query($params);
                }
            } else {
                if (!empty($payload)) {
                    $requestOptions['json'] = $payload;
                }
            }
            
            // For Google Gemini, add API key to URL if not in headers
            if ($tempModel->provider === 'google' && !isset($headers['x-goog-api-key']) && !empty($tempModel->api_key)) {
                $url .= (strpos($url, '?') !== false ? '&' : '?') . 'key=' . urlencode($tempModel->api_key);
            }
            
            $response = $client->request($this->getMethod($tempModel), $url, $requestOptions);
            
            $responseBody = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();
            
            return [
                'success' => true,
                'status_code' => $statusCode,
                'response' => json_decode($responseBody, true) ?? $responseBody,
                'raw_response' => $responseBody
            ];
            
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorBody = '';
            if ($response) {
                try {
                    $errorBody = $response->getBody()->getContents();
                } catch (\Exception $ex) {
                    $errorBody = $e->getMessage();
                }
            }
            return [
                'success' => false,
                'status_code' => $response ? $response->getStatusCode() : 0,
                'error' => $e->getMessage(),
                'response' => !empty($errorBody) ? (json_decode($errorBody, true) ?? $errorBody) : null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function test($id, $testPrompt, $formData = null)
    {
        try {
            $model = $this->model->findOrFail($id);
            
            // Use form data if provided, otherwise use saved model data
            $apiKey = $formData['api_key'] ?? $model->api_key;
            $baseUrl = $formData['base_url'] ?? $model->base_url;
            $modelName = $formData['model_name'] ?? $model->model_name;
            $provider = $formData['provider'] ?? $model->provider;
            
            if (empty($apiKey)) {
                throw new Exception("API Key is required to test the model.");
            }
            
            // Create a temporary model object with form data for placeholder replacement
            $tempModel = clone $model;
            $tempModel->api_key = $apiKey;
            $tempModel->base_url = $baseUrl;
            $tempModel->model_name = $modelName;
            $tempModel->provider = $provider;
            
            // Prepare headers from form or model
            if ($formData && isset($formData['header_key']) && isset($formData['header_value'])) {
                $headers = $this->extractKeyValuePairs($formData, 'header_key', 'header_value');
            } else {
                $headers = $model->headers ?? [];
            }
            $headers = $this->replacePlaceholders($headers, $tempModel);
            
            // Add default headers if empty
            if (empty($headers)) {
                $headers = [
                    'Content-Type' => 'application/json',
                ];
                
                // Add Authorization header based on provider
                if ($provider === 'openai' || $provider === 'anthropic') {
                    $headers['Authorization'] = 'Bearer ' . $apiKey;
                } elseif ($provider === 'google') {
                    // Google Gemini uses API key in URL params or header
                    $headers['x-goog-api-key'] = $apiKey;
                } else {
                    $headers['Authorization'] = 'Bearer ' . $apiKey;
                }
            }
            
            // Prepare params from form or model
            if ($formData && isset($formData['param_key']) && isset($formData['param_value'])) {
                $params = $this->extractKeyValuePairs($formData, 'param_key', 'param_value');
            } else {
                $params = $model->params ?? [];
            }
            $params = $this->replacePlaceholders($params, $tempModel);
            
            // Prepare payload from form or model
            if ($formData && isset($formData['payload'])) {
                $decodedPayload = json_decode($formData['payload'], true);
                if (!empty($decodedPayload) && is_array($decodedPayload)) {
                    $payload = $decodedPayload;
                } else {
                    $payload = [];
                }
            } elseif ($formData && isset($formData['payload_key']) && isset($formData['payload_value'])) {
                $payload = $this->extractKeyValuePairs($formData, 'payload_key', 'payload_value');
            } else {
                $payload = $model->payload ?? [];
            }
            
            // If payload is empty, create default payload based on provider
            if (empty($payload)) {
                $payload = $this->getDefaultPayload($tempModel, $testPrompt);
            } else {
                $payload = $this->replacePlaceholders($payload, $tempModel);
                // Replace prompt placeholder in payload
                $payload = $this->replacePromptInPayload($payload, $testPrompt);
            }
            
            // Determine endpoint based on provider
            $endpoint = $this->getEndpoint($tempModel);
            
            // Prepare full URL
            if ($baseUrl) {
                // If base_url is provided, use it and append endpoint
                $baseUrlClean = rtrim($baseUrl, '/');
                // Check if base_url already includes /v1
                $hasV1 = (substr($baseUrlClean, -3) === '/v1');
                // If endpoint starts with /v1 and base_url already has /v1, remove /v1 from endpoint
                if ($hasV1 && strpos($endpoint, '/v1/') === 0) {
                    $endpoint = substr($endpoint, 3); // Remove /v1 from start
                }
                $url = $baseUrlClean . $endpoint;
            } else {
                // Use default URL for provider
                $url = $this->getDefaultUrl($tempModel) . $endpoint;
            }
            
            // Make HTTP request
            $client = new Client();
            
            $requestOptions = [
                'headers' => $headers,
                'timeout' => 30,
            ];
            
            // Add params to URL if GET, or to body if POST
            if (strtoupper($this->getMethod($model)) === 'GET') {
                if (!empty($params)) {
                    $url .= '?' . http_build_query($params);
                }
            } else {
                if (!empty($payload)) {
                    $requestOptions['json'] = $payload;
                }
            }
            
            // For Google Gemini, add API key to URL if not in headers
            if ($provider === 'google' && !isset($headers['x-goog-api-key']) && !empty($apiKey)) {
                $url .= (strpos($url, '?') !== false ? '&' : '?') . 'key=' . urlencode($apiKey);
            }
            
            $response = $client->request($this->getMethod($model), $url, $requestOptions);
            
            $responseBody = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();
            
            return [
                'success' => true,
                'status_code' => $statusCode,
                'response' => json_decode($responseBody, true) ?? $responseBody,
                'raw_response' => $responseBody
            ];
            
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorBody = '';
            if ($response) {
                try {
                    $errorBody = $response->getBody()->getContents();
                } catch (\Exception $ex) {
                    $errorBody = $e->getMessage();
                }
            }
            return [
                'success' => false,
                'status_code' => $response ? $response->getStatusCode() : 0,
                'error' => $e->getMessage(),
                'response' => !empty($errorBody) ? (json_decode($errorBody, true) ?? $errorBody) : null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getDefaultPayload($model, $prompt)
    {
        switch ($model->provider) {
            case 'openai':
                return [
                    'model' => $model->model_name ?? 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1000
                ];
            case 'google':
                return [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 8192
                    ]
                ];
            case 'anthropic':
                return [
                    'model' => $model->model_name ?? 'claude-3-sonnet-20240229',
                    'max_tokens' => 1024,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ];
            default:
                return [
                    'prompt' => $prompt,
                    'model' => $model->model_name ?? 'default'
                ];
        }
    }
    
    private function replacePlaceholders($data, $model)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->replacePlaceholders($value, $model);
                } else {
                    $data[$key] = str_replace(
                        ['{api_key}', '{model_name}', '{base_url}'],
                        [$model->api_key ?? '', $model->model_name ?? '', $model->base_url ?? ''],
                        $value
                    );
                }
            }
        }
        return $data;
    }
    
    private function replacePromptInPayload($payload, $prompt)
    {
        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                if (is_array($value)) {
                    $payload[$key] = $this->replacePromptInPayload($value, $prompt);
                } else {
                    $payload[$key] = str_replace('{prompt}', $prompt, $value);
                }
            }
        }
        return $payload;
    }
    
    private function getEndpoint($model)
    {
        switch ($model->provider) {
            case 'openai':
                return '/v1/chat/completions';
            case 'google':
                $modelName = $model->model_name ?? 'gemini-pro';
                // Support both v1 and v1beta endpoints
                // Default to v1beta for newer models like gemini-pro
                return '/v1beta/models/' . $modelName . ':generateContent';
            case 'anthropic':
                return '/v1/messages';
            default:
                return '/v1/chat/completions';
        }
    }
    
    private function getDefaultUrl($model)
    {
        switch ($model->provider) {
            case 'openai':
                return 'https://api.openai.com';
            case 'google':
                return 'https://generativelanguage.googleapis.com';
            case 'anthropic':
                return 'https://api.anthropic.com';
            default:
                return 'https://api.openai.com';
        }
    }
    
    private function getMethod($model)
    {
        // Most AI APIs use POST
        return 'POST';
    }


    public function generateTitle($currentTitle, $locale = null, $titleType = 'service')
    {
        try {
            // Get the default AI model - use $this->model to access the repository model
            $defaultModel = $this->model->where('is_default', true)->first();
            
            if (!$defaultModel) {
                // If no default model, try to get the first available model
                $defaultModel = $this->model->first();
                
                if (!$defaultModel) {
                    throw new Exception("No AI model configured. Please configure an AI model first.");
                }
            }

            // Create prompt based on your requirements
            $prompt = $this->createTitlePrompt($currentTitle, $titleType);
            
            // Test the AI model with the prompt
            // Note: We're calling the test method with only 2 parameters as per your existing test method
            $result = $this->test($defaultModel->id, $prompt);
            
            if (!$result['success']) {
                throw new Exception("AI Model Error: " . ($result['error'] ?? 'Unknown error'));
            }

            // Extract and validate the response
            $generatedTitle = $this->extractGeneratedTitle($result, $defaultModel->provider);
            
            if ($generatedTitle === 'INVALID_TITLE') {
                return [
                    'status' => false,
                    'message' => 'The input title is not relevant to the specified title type.',
                    'data' => null
                ];
            }

            return [
                'status' => true,
                'message' => 'Title generated successfully',
                'data' => $generatedTitle
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    private function createTitlePrompt($currentTitle, $titleType)
    {
        return <<<PROMPT
        You are generating a professional title for a "{$titleType}" in a marketplace application.

        Rules:
        - Generate ONLY a relevant title for the given type.
        - No vulnerable, abusive, sexual, violent, misleading, promotional, or unsafe words.
        - No pricing, offers, emojis, symbols, or special characters.
        - No brand names, personal names, or locations.
        - Output must be short, clear, and professional.

        Input title:
        "{$currentTitle}"

        Validation:
        - If input is NOT relevant to the given title type, respond exactly with:
        "INVALID_TITLE"

        Output:
        - Return ONLY ONE refined title.
        PROMPT;
    }

    private function extractGeneratedTitle($result, $provider)
    {
        if (!isset($result['response'])) {
            throw new Exception("No response received from AI model");
        }

        $response = $result['response'];
        
        // Extract title based on provider response format
        switch ($provider) {
            case 'openai':
                if (isset($response['choices'][0]['message']['content'])) {
                    $title = trim($response['choices'][0]['message']['content']);
                    // Clean up the response - remove quotes if present
                    $title = trim($title, '\'"');
                    return $title;
                }
                break;
                
            case 'google':
                if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                    $title = trim($response['candidates'][0]['content']['parts'][0]['text']);
                    $title = trim($title, '\'"');
                    return $title;
                }
                break;
                
            case 'anthropic':
                if (isset($response['content'][0]['text'])) {
                    $title = trim($response['content'][0]['text']);
                    $title = trim($title, '\'"');
                    return $title;
                }
                break;
                
            default:
                // For custom providers, try to find text in response
                if (is_string($response)) {
                    $title = trim($response);
                    $title = trim($title, '\'"');
                    return $title;
                } elseif (is_array($response)) {
                    // Try to find the first string value
                    foreach ($response as $value) {
                        if (is_string($value)) {
                            $title = trim($value);
                            $title = trim($title, '\'"');
                            return $title;
                        }
                    }
                }
        }
        
        // If we can't extract from structured response, use raw response
        if (isset($result['raw_response'])) {
            $raw = $result['raw_response'];
            if (is_string($raw)) {
                $title = trim($raw);
                $title = trim($title, '\'"');
                return $title;
            }
        }
        
        throw new Exception("Could not extract title from AI response");
    }
}
