<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CustomAIModel;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\DataTables\CustomAIDataTable;
use App\Repositories\Backend\CustomAIModelRepository;
use App\Http\Requests\Backend\CreateCustomAIModelRequest;
use App\Http\Requests\Backend\UpdateCustomAIModelRequest;
use App\Http\Traits\AIContentGenerationTrait;

class CustomAIModelController extends Controller
{
    public $repository;
    use AIContentGenerationTrait;

    public function __construct(CustomAIModelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(CustomAIDataTable $dataTable)
    {
        return $this->repository->index($dataTable);
    }

    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateCustomAIModelRequest $request)
    {
        // Validation passed, get all request data (including arrays like header_key[], param_key[], etc.)
        return $this->repository->store($request->all());
    }

    public function edit($custom_ai_model)
    {
        return $this->repository->edit($custom_ai_model);
    }

    public function update(UpdateCustomAIModelRequest $request, $custom_ai_model)
    {
        // Validation passed, get all request data (including arrays like header_key[], param_key[], etc.)
        return $this->repository->update($request->all(), $custom_ai_model);
    }

    public function destroy($custom_ai_model)
    {
        return $this->repository->destroy($custom_ai_model);
    }

    public function setDefault($custom_ai_model)
    {
        return $this->repository->setDefault($custom_ai_model);
    }

    public function testCreate(Request $request)
    {
        $request->validate([
            'test_prompt' => 'required|string',
            'provider' => 'required|string|in:openai,google,anthropic,custom',
            'api_key' => 'required|string'
        ]);

        // Get all form data to use latest values
        $formData = $request->except(['_token', 'test_prompt']);
        
        $result = $this->repository->testCreate($request->test_prompt, $formData);
        
        return response()->json($result);
    }

    public function test(Request $request, $custom_ai_model)
    {
        $request->validate([
            'test_prompt' => 'required|string'
        ]);

        // Get all form data to use latest values
        $formData = $request->except(['_token', 'test_prompt']);
        
        $result = $this->repository->test($custom_ai_model, $request->test_prompt, $formData);
        
        return response()->json($result);
    }


    /**
     * Generate title using AI with dynamic prompt
     */
    public function generateTitle(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'prompt' => 'nullable|string',
            'locale' => 'nullable|string',
            'content_type' => 'nullable|string|in:service,service_package,product,category,blog,post,page,coupon,subscription_plan,service_request,announcement_backend,announcement_frontend',
            'length' => 'nullable|integer|min:10|max:200' // Max length for title in characters
        ]);

        // Set application locale for AI prompts if provided
        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $context = [
            'content_type' => $request->content_type ?? 'service'
        ];

        // Add length to context if provided
        if ($request->filled('length')) {
            $context['max_length'] = (int) $request->length;
        }

        $result = $this->generateAITitle(
            $request->value,
            $request->prompt, // Dynamic prompt template
            $context
        );
        
        return response()->json($result);
    }

    /**
     * Generate description using AI with dynamic prompt (plain text)
     */
    public function generateDescription(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'prompt' => 'nullable|string',
            'locale' => 'nullable|string',
            'content_type' => 'nullable|string|in:service,service_package,product,category,blog,post,page',
            'title' => 'nullable|string' // Additional context for description generation
        ]);

        // Set application locale for AI prompts if provided
        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $context = [
            'content_type' => $request->content_type ?? 'service'
        ];
        
        if ($request->title) {
            $context['title'] = $request->title;
        }

        $result = $this->generateAIDescription(
            $request->value,
            $request->prompt, // Dynamic prompt template
            $context
        );
        
        return response()->json($result);
    }

    /**
     * Generate HTML-based content using AI with dynamic prompt
     */
    public function generateContent(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'prompt' => 'nullable|string',
            'locale' => 'nullable|string',
            'content_type' => 'nullable|string|in:service,service_package,product,category,blog,post,page',
            'title' => 'nullable|string' // Additional context for content generation
        ]);

        // Set application locale for AI prompts if provided
        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $context = [
            'content_type' => $request->content_type ?? 'service'
        ];
        
        if ($request->title) {
            $context['title'] = $request->title;
        }

        $result = $this->generateAIContent(
            $request->value,
            $request->prompt, // Dynamic prompt template
            $context
        );
        
        return response()->json($result);
    }

    public function generateFAQ(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'locale' => 'nullable|string',
            'content_type' => 'nullable|string|in:service,product,category,blog,post,page',
            'count' => 'nullable|integer|min:1|max:10',
        ]);

        // Set application locale for AI prompts if provided
        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $count = $request->count ?? 5;

        $result = $this->generateAIFAQ(
            $request->title,
            null, // promptTemplate
            $request->content_type ?? 'service',
            $count
        );

        return response()->json($result);
    }

    /**
     * Generate meta description using AI
     */
    public function generateMetaDescription(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'locale' => 'nullable|string',
            'content_type' => 'nullable|string|in:service,product,category,blog,post,page'
        ]);

        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $result = $this->generateAIMetaDescription(
            $request->value,
            null,
            $request->content_type ?? 'service'
        );
        
        return response()->json($result);
    }

    /**
     * Generate keywords using AI
     */
    public function generateKeywords(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'locale' => 'nullable|string',
            'content_type' => 'nullable|string|in:service,product,category,blog,post,page',
            'max_keywords' => 'nullable|integer|min:1|max:10'
        ]);

        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $result = $this->generateAIKeywords(
            $request->value,
            null,
            $request->content_type ?? 'service',
            $request->max_keywords ?? 5
        );
        
        return response()->json($result);
    }

    /**
     * Generate content batch (title, description, meta, keywords)
     */
    public function generateContentBatch(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'locale' => 'nullable|string',
            'content_type' => 'nullable|string|in:service,product,category,blog,post,page',
            'generate' => 'nullable|array|in:title,description,meta,keywords'
        ]);

        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $result = $this->generateAIContentBatch(
            $request->value,
            $request->content_type ?? 'service',
            $request->generate ?? ['title', 'description', 'meta', 'keywords']
        );
        
        return response()->json($result);
    }

    /**
     * Legacy methods - kept for backward compatibility
     * These now use the unified generateTitle and generateDescription methods
     */
    public function generateCategoryTitle(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'prompt' => 'nullable|string',
            'locale' => 'nullable|string'
        ]);

        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $context = ['content_type' => 'category'];
        
        // Use category title prompt if no custom prompt provided
        $prompt = $request->prompt;
        if (!$prompt) {
            // Default category title prompt will be used via getTitlePrompt
        }

        $result = $this->generateAITitle(
            $request->value,
            $prompt,
            $context
        );
        
        return response()->json($result);
    }

    public function generateCategoryDescription(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'prompt' => 'nullable|string',
            'locale' => 'nullable|string',
            'title' => 'nullable|string'
        ]);

        if ($request->filled('locale')) {
            app()->setLocale($request->locale);
        }

        $context = ['content_type' => 'category'];
        
        if ($request->title) {
            $context['title'] = $request->title;
        }

        $result = $this->generateAIDescription(
            $request->value,
            $request->prompt,
            $context
        );
        
        return response()->json($result);
    }
}
