<?php

namespace App\Http\Traits;

use Exception;
use App\Models\CustomAIModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

trait AIContentGenerationTrait
{
    /**
     * Generate title using AI with dynamic prompt
     *
     * @param string $input The input value (title, name, etc.)
     * @param string|null $dynamicPrompt Custom prompt template with placeholders (e.g., {{INPUT}})
     * @param array $context Additional context for prompt replacement
     * @return array
     */
    public function generateAITitle($input, $dynamicPrompt = null, $context = [])
    {
        try {
            // Get the default AI model
            $defaultModel = CustomAIModel::where('is_default', true)->first();
            
            if (!$defaultModel) {
                // If no default model, try to get the first available model
                $defaultModel = CustomAIModel::first();
                
                if (!$defaultModel) {
                    throw new Exception("No AI model configured. Please configure an AI model first.");
                }
            }

            // Use dynamic prompt if provided, otherwise use default
            if ($dynamicPrompt) {
                $prompt = $this->replacePromptPlaceholders($dynamicPrompt, array_merge(['INPUT' => $input], $context));
                // Add length constraint to dynamic prompt if provided
                if (isset($context['max_length']) && strpos($prompt, 'max') === false && strpos($prompt, 'length') === false) {
                    $prompt = str_replace(
                        'Rules:',
                        "Rules:\n- Maximum length: {$context['max_length']} characters",
                        $prompt
                    );
                }
            } else {
                // Fallback to default title prompt
                $titleType = $context['content_type'] ?? 'service';
                $maxLength = $context['max_length'] ?? null;
                $prompt = $this->getTitlePrompt($input, null, $titleType, $maxLength);
            }
            
            // Test the AI model with the prompt
            $result = $this->testAIModel($defaultModel, $prompt);
            
            if (!$result['success']) {
                throw new Exception("AI Model Error: " . ($result['error'] ?? 'Unknown error'));
            }

            // Extract and validate the response
            $generatedTitle = $this->extractAIContent($result, $defaultModel->provider);
            
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

    /**
     * Replace placeholders in prompt template
     * 
     * @param string $promptTemplate The prompt template with placeholders
     * @param array $context Array of values to replace (e.g., ['INPUT' => 'value', 'TITLE' => 'title'])
     * @return string
     */
    private function replacePromptPlaceholders($promptTemplate, $context = [])
    {
        $prompt = $promptTemplate;
        
        // Replace all placeholders in format {{KEY}}
        foreach ($context as $key => $value) {
            $placeholder = '{{' . strtoupper($key) . '}}';
            $prompt = str_replace($placeholder, $value, $prompt);
        }
        
        // Also support lowercase placeholders
        foreach ($context as $key => $value) {
            $placeholder = '{{' . strtolower($key) . '}}';
            $prompt = str_replace($placeholder, $value, $prompt);
        }
        
        return $prompt;
    }

    /**
     * Get default HTML content prompt
     * 
     * @param string $input The input value
     * @param string $contentType The content type (service, blog, category, etc.)
     * @param array $context Additional context
     * @return string
     */
    private function getContentPrompt($input, $contentType = 'service', $context = [])
    {
        $title = $context['title'] ?? '';
        $additionalContext = '';
        $locale = request('locale', app()->getLocale());
        
        if ($title) {
            $additionalContext = "\nTitle: \"{$title}\"";
        }

        return <<<PROMPT
Generate professional HTML-based content for a "{$contentType}" based on this input:{$additionalContext}
"{$input}"

Rules:
- Write 3–6 short paragraphs using valid HTML <p>...</p> tags
- Do NOT use bullet points, numbered lists, markdown (**bold**), or headings unless explicitly required
- Clearly explain the content, key benefits, and important information
- Keep a professional, friendly tone suitable for a web page
- Do NOT repeat the title in every paragraph; mention it naturally only where needed
- No personal names, brand names, or locations unless already present in the input
- Write the content in the "{$locale}" language
- If the input is irrelevant, respond exactly with: "INVALID_CONTENT"

Output:
- Return ONLY the HTML content with multiple <p>...</p> blocks, nothing else.
PROMPT;
    }

    private function getPromptByType(
        string $content,
        string $type,
        ?string $template = null,
        array $context = []
    ) {
        return match ($type) {
            'category_title' =>
                $this->getCategoryTitlePrompt($content, $template),

            'category_description' =>
                $this->getCategoryDescriptionPrompt($content, $template, $context),

            default =>
                throw new Exception("Unsupported AI content type: {$type}")
        };
    }

    /**
     * Generate description using AI with dynamic prompt (plain text)
     *
     * @param string $input The input value (description, content, etc.)
     * @param string|null $dynamicPrompt Custom prompt template with placeholders (e.g., {{INPUT}})
     * @param array $context Additional context for prompt replacement
     * @return array
     */
    public function generateAIDescription($input, $dynamicPrompt = null, $context = [])
    {
        try {
            // Get the default AI model
            $defaultModel = CustomAIModel::where('is_default', true)->first();
            
            if (!$defaultModel) {
                // If no default model, try to get the first available model
                $defaultModel = CustomAIModel::first();
                
                if (!$defaultModel) {
                    throw new Exception("No AI model configured. Please configure an AI model first.");
                }
            }

            // Use dynamic prompt if provided, otherwise use default
            if ($dynamicPrompt) {
                $prompt = $this->replacePromptPlaceholders($dynamicPrompt, array_merge(['INPUT' => $input], $context));
            } else {
                // Fallback to default description prompt
                $contentType = $context['content_type'] ?? 'service';
                $prompt = $this->getDescriptionPrompt($input, null, $contentType, $context);
            }
            
            // Test the AI model with the prompt
            $result = $this->testAIModel($defaultModel, $prompt);
            
            if (!$result['success']) {
                throw new Exception("AI Model Error: " . ($result['error'] ?? 'Unknown error'));
            }

            // Extract the response
            $generatedDescription = $this->extractAIContent($result, $defaultModel->provider);
            
            if ($generatedDescription === 'INVALID_DESCRIPTION') {
                return [
                    'status' => false,
                    'message' => 'The input description is not relevant to the specified content type.',
                    'data' => null
                ];
            }

            return [
                'status' => true,
                'message' => 'Description generated successfully',
                'data' => $generatedDescription
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate HTML-based content using AI with dynamic prompt
     *
     * @param string $input The input value (title, description, etc.)
     * @param string|null $dynamicPrompt Custom prompt template with placeholders (e.g., {{INPUT}})
     * @param array $context Additional context for prompt replacement
     * @return array
     */
    public function generateAIContent($input, $dynamicPrompt = null, $context = [])
    {
        try {
            // Get the default AI model
            $defaultModel = CustomAIModel::where('is_default', true)->first();
            
            if (!$defaultModel) {
                // If no default model, try to get the first available model
                $defaultModel = CustomAIModel::first();
                
                if (!$defaultModel) {
                    throw new Exception("No AI model configured. Please configure an AI model first.");
                }
            }

            // Use dynamic prompt if provided, otherwise use default HTML content prompt
            if ($dynamicPrompt) {
                $prompt = $this->replacePromptPlaceholders($dynamicPrompt, array_merge(['INPUT' => $input], $context));
            } else {
                // Fallback to default HTML content prompt
                $contentType = $context['content_type'] ?? 'service';
                $prompt = $this->getContentPrompt($input, $contentType, $context);
            }
            
            // Test the AI model with the prompt
            $result = $this->testAIModel($defaultModel, $prompt);
            
            if (!$result['success']) {
                throw new Exception("AI Model Error: " . ($result['error'] ?? 'Unknown error'));
            }

            // Extract the response
            $generatedContent = $this->extractAIContent($result, $defaultModel->provider);
            
            if ($generatedContent === 'INVALID_DESCRIPTION' || $generatedContent === 'INVALID_CONTENT') {
                return [
                    'status' => false,
                    'message' => 'The input content is not relevant to the specified content type.',
                    'data' => null
                ];
            }

            return [
                'status' => true,
                'message' => 'Content generated successfully',
                'data' => $generatedContent
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate meta description using AI
     *
     * @param string $content
     * @param string $promptTemplate
     * @param string $contentType
     * @return array
     */
    public function generateAIMetaDescription($content, $promptTemplate = null, $contentType = 'service')
    {
        try {
            $defaultModel = CustomAIModel::where('is_default', true)->first();
            
            if (!$defaultModel) {
                $defaultModel = CustomAIModel::first();
                
                if (!$defaultModel) {
                    throw new Exception("No AI model configured.");
                }
            }

            $prompt = $this->getMetaDescriptionPrompt($content, $promptTemplate, $contentType);
            $result = $this->testAIModel($defaultModel, $prompt);
            
            if (!$result['success']) {
                throw new Exception("AI Model Error: " . ($result['error'] ?? 'Unknown error'));
            }

            $metaDescription = $this->extractAIContent($result, $defaultModel->provider);
            
            return [
                'status' => true,
                'message' => 'Meta description generated successfully',
                'data' => $metaDescription
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate keywords/tags using AI
     *
     * @param string $content
     * @param string $promptTemplate
     * @param string $contentType
     * @param int $maxKeywords
     * @return array
     */
    public function generateAIKeywords($content, $promptTemplate = null, $contentType = 'service', $maxKeywords = 5)
    {
        try {
            $defaultModel = CustomAIModel::where('is_default', true)->first();
            
            if (!$defaultModel) {
                $defaultModel = CustomAIModel::first();
                
                if (!$defaultModel) {
                    throw new Exception("No AI model configured.");
                }
            }

            $prompt = $this->getKeywordsPrompt($content, $promptTemplate, $contentType, $maxKeywords);
            $result = $this->testAIModel($defaultModel, $prompt);
            
            if (!$result['success']) {
                throw new Exception("AI Model Error: " . ($result['error'] ?? 'Unknown error'));
            }

            $keywords = $this->extractAIContent($result, $defaultModel->provider);
            
            // Convert keywords string to array if needed
            if (is_string($keywords)) {
                $keywords = $this->parseKeywordsString($keywords);
            }

            return [
                'status' => true,
                'message' => 'Keywords generated successfully',
                'data' => $keywords
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get the appropriate title prompt template
     */
    private function getTitlePrompt($currentTitle, $promptTemplate = null, $titleType = 'service', $maxLength = null)
    {
        // Use custom prompt template if provided
        if ($promptTemplate) {
            $prompt = str_replace('{{CURRENT_TITLE}}', $currentTitle, $promptTemplate);
            // Add length constraint if provided
            if ($maxLength) {
                $prompt = str_replace(
                    'Rules:',
                    "Rules:\n- Maximum length: {$maxLength} characters",
                    $prompt
                );
            }
            return $prompt;
        }

        // Use default prompts based on title type
        $prompts = [
            'service' => $this->getServiceTitlePrompt($currentTitle, $maxLength),
            'product' => $this->getProductTitlePrompt($currentTitle, $maxLength),
            'category' => $this->getCategoryTitlePrompt($currentTitle, null, $maxLength),
            'blog' => $this->getBlogTitlePrompt($currentTitle, $maxLength),
            'post' => $this->getBlogTitlePrompt($currentTitle, $maxLength),
            'page' => $this->getPageTitlePrompt($currentTitle, $maxLength),
            'announcement_backend' => $this->getAnnouncementBackendTitlePrompt($currentTitle, $maxLength),
            'announcement_frontend' => $this->getAnnouncementFrontendTitlePrompt($currentTitle, $maxLength),
            'default' => $this->getDefaultTitlePrompt($currentTitle, $titleType, $maxLength),
        ];

        return $prompts[$titleType] ?? $prompts['default'];
    }

    /**
     * Get the appropriate description prompt template
     */
    private function getDescriptionPrompt($currentDescription, $promptTemplate = null, $contentType = 'service', $context = [])
    {
        // Use custom prompt template if provided
        if ($promptTemplate) {
            $prompt = str_replace('{{CURRENT_DESCRIPTION}}', $currentDescription, $promptTemplate);
            
            // Replace additional context placeholders
            foreach ($context as $key => $value) {
                $placeholder = '{{' . strtoupper($key) . '}}';
                $prompt = str_replace($placeholder, $value, $prompt);
            }
            
            return $prompt;
        }

        // Use default prompts based on content type
        $prompts = [
            'service' => $this->getServiceDescriptionPrompt($currentDescription, $context),
            'service_package' => $this->getServicePackageDescriptionPrompt($currentDescription, $context),
            'product' => $this->getProductDescriptionPrompt($currentDescription, $context),
            'category' => $this->getCategoryDescriptionPrompt($currentDescription),
            'blog' => $this->getBlogDescriptionPrompt($currentDescription),
            'post' => $this->getBlogDescriptionPrompt($currentDescription),
            'page' => $this->getPageDescriptionPrompt($currentDescription),
            'default' => $this->getDefaultDescriptionPrompt($currentDescription, $contentType),
        ];

        return $prompts[$contentType] ?? $prompts['default'];
    }

    /**
     * Get meta description prompt
     */
    private function getMetaDescriptionPrompt($content, $promptTemplate = null, $contentType = 'service')
    {
        if ($promptTemplate) {
            return str_replace('{{CONTENT}}', $content, $promptTemplate);
        }

        return <<<PROMPT
Generate a compelling meta description for a "{$contentType}" with this content:
"{$content}"

Rules:
- Keep it between 120-160 characters
- Include primary keywords naturally
- Make it engaging and click-worthy
- Use active voice
- No promotional language or exclamation marks
- End with a period

Output ONLY the meta description:
PROMPT;
    }

    /**
     * Get keywords prompt
     */
    private function getKeywordsPrompt($content, $promptTemplate = null, $contentType = 'service', $maxKeywords = 5)
    {
        if ($promptTemplate) {
            return str_replace(['{{CONTENT}}', '{{MAX_KEYWORDS}}'], [$content, $maxKeywords], $promptTemplate);
        }

        return <<<PROMPT
Generate {$maxKeywords} relevant keywords/tags for a "{$contentType}" with this content:
"{$content}"

Rules:
- Generate exactly {$maxKeywords} keywords
- Make them relevant and searchable
- Use comma-separated format
- No quotation marks
- Include both short-tail and long-tail keywords
- Prioritize SEO value

Output ONLY the keywords in comma-separated format:
PROMPT;
    }

    // ============================================
    // TITLE PROMPT TEMPLATES
    // ============================================

    private function getServiceTitlePrompt($currentTitle, $maxLength = null)
    {
        $locale = request('locale', app()->getLocale());
        $lengthConstraint = $maxLength ? "- Maximum length: {$maxLength} characters" : "- Make it descriptive but concise (max 60 characters)";

        return <<<PROMPT
Generate a clear and professional service title based on this input:
"{$currentTitle}"

Rules:
{$lengthConstraint}
- Keep the title closely related to the input - do not change the core meaning
- Use simple, clear language that directly describes the service
- If input is "car cleaning", output should be something like "Car Cleaning Service" or "Professional Car Cleaning"
- If input is "plumbing repair", output should be something like "Plumbing Repair Service" or "Professional Plumbing Repair"
- Do NOT use overly fancy or marketing terms like "Ultimate", "Premium", "Refinement", "Detailing" unless the input specifically mentions them
- Keep it straightforward and professional
- Use title case (capitalize first letter of each major word)
- No personal names or human names
- No brand names, personal names, or locations
- Write the title in the "{$locale}" language
- If the input is irrelevant to a service, respond with: "INVALID_TITLE"

Output ONLY the title:
PROMPT;
    }

    private function getProductTitlePrompt($currentTitle, $maxLength = null)
    {
        $locale = request('locale', app()->getLocale());
        $lengthConstraint = $maxLength ? "- Maximum length: {$maxLength} characters" : "- Max 70 characters";

        return <<<PROMPT
Generate an e-commerce product title based on this input:
"{$currentTitle}"

Rules:
- Include key features if relevant
{$lengthConstraint}
- Search-engine friendly
- Proper capitalization
- No personal names or human names
- No brand names, personal names, or locations
- Write the title in the "{$locale}" language
- If irrelevant, respond with: "INVALID_TITLE"

Output ONLY the title:
PROMPT;
    }

    private function getCategoryTitlePrompt($name, $template = null, $maxLength = null) 
    {
        $locale = request('locale', app()->getLocale());

        if ($template) {
            $prompt = str_replace('{{NAME}}', $name, $template);
            if ($maxLength) {
                $prompt = str_replace(
                    'Rules:',
                    "Rules:\n- Maximum length: {$maxLength} characters",
                    $prompt
                );
            }
            return $prompt;
        }

        $lengthConstraint = $maxLength ? "- Maximum length: {$maxLength} characters" : "- Keep it under 60 characters";

        return <<<PROMPT
        You are generating a category title for a service marketplace.

        Category name:
        "{$name}"

        Rules:
        - Generate ONE clear, SEO-friendly category title.
        {$lengthConstraint}
        - Use proper capitalization.
        - Do NOT add pricing, locations, or brand names.
        - Do NOT add emojis or special characters.

        Return ONLY the title text.
        PROMPT;
    }

    private function getCategoryDescriptionPrompt(
        string $name,
        ?string $template = null,
        array $context = []
    ) {
        if ($template) {
            return str_replace('{{NAME}}', $name, $template);
        }
    
        $locale = request('locale', app()->getLocale());
    
        return <<<PROMPT
            You are writing a category description for a service marketplace.
            
            Category title:
            "{$name}"
            
            Guidelines:
            - Write 2–3 short paragraphs.
            - Explain what services belong to this category.
            - Highlight common use cases and benefits.
            - Keep the tone professional and customer-friendly.
            - Do NOT mention prices, discounts, or locations.
            - Do NOT mention brand names or individuals.
            - Optimize naturally for search engines.
            - Write in "{$locale}" language.
            
            Return plain text only. Do NOT use markdown or HTML.
            PROMPT;
    }

    private function getBlogTitlePrompt($currentTitle, $maxLength = null)
    {
        $locale = request('locale', app()->getLocale());
        $lengthConstraint = $maxLength ? "- Maximum length: {$maxLength} characters" : "- Engaging and click-worthy (max 80 characters)";

        return <<<PROMPT
        Generate an engaging blog post title based on this input:
        "{$currentTitle}"

        Rules:
        {$lengthConstraint}
        - Use power words when appropriate
        - SEO optimized
        - Title case
        - No personal names or human names
        - No brand names, personal names, or locations
        - Write the title in the "{$locale}" language
        - If irrelevant, respond with: "INVALID_TITLE"

        Output ONLY the title:
        PROMPT;
    }

    private function getPageTitlePrompt($currentTitle, $maxLength = null)
    {
        $locale = request('locale', app()->getLocale());
        $lengthConstraint = $maxLength ? "- Maximum length: {$maxLength} characters" : "- Descriptive and SEO-friendly (max 60 characters)";

        return <<<PROMPT
        Generate a web page title based on this input:
        "{$currentTitle}"

        Rules:
        {$lengthConstraint}
        - Include primary keyword
        - Title case
        - No personal names or human names
        - No brand names, personal names, or locations
        - Write the title in the "{$locale}" language
        - If irrelevant, respond with: "INVALID_TITLE"

        Output ONLY the title:
        PROMPT;
    }

    private function getAnnouncementBackendTitlePrompt($currentTitle, $maxLength = null)
    {
        $locale = request('locale', app()->getLocale());
        $lengthConstraint = $maxLength ? "- Maximum length: {$maxLength} characters" : "- Short and clear (max 80 characters)";

        return <<<PROMPT
Generate a short announcement bar title for an admin/backend dashboard based on this input:
"{$currentTitle}"

Rules:
{$lengthConstraint}
- Suitable for a backend admin panel announcement bar (e.g. welcome message, notice, offer)
- Professional and clear
- No personal names or brand names
- Write in the "{$locale}" language
- If irrelevant, respond with: "INVALID_TITLE"

Output ONLY the title:
PROMPT;
    }

    private function getAnnouncementFrontendTitlePrompt($currentTitle, $maxLength = null)
    {
        $locale = request('locale', app()->getLocale());
        $lengthConstraint = $maxLength ? "- Maximum length: {$maxLength} characters" : "- Short and catchy (max 80 characters)";

        return <<<PROMPT
Generate a short announcement bar title for a customer-facing website frontend based on this input:
"{$currentTitle}"

Rules:
{$lengthConstraint}
- Suitable for a frontend site announcement bar (e.g. welcome, promo, news, CTA)
- Engaging and customer-friendly
- No personal names or brand names unless in input
- Write in the "{$locale}" language
- If irrelevant, respond with: "INVALID_TITLE"

Output ONLY the title:
PROMPT;
    }

    private function getDefaultTitlePrompt($currentTitle, $titleType, $maxLength = null)
    {
        $locale = request('locale', app()->getLocale());
        $lengthConstraint = $maxLength ? "- Maximum length: {$maxLength} characters" : "- Short, clear, and professional";

        return <<<PROMPT
Generate a professional title for a "{$titleType}" based on this input:
"{$currentTitle}"

Rules:
{$lengthConstraint}
- No unsafe or misleading content
- No personal names or human names
- No brand names, personal names, or locations
- Write the title in the "{$locale}" language
- If irrelevant, respond with: "INVALID_TITLE"

Output ONLY the title:
PROMPT;
    }

    // ============================================
    // DESCRIPTION PROMPT TEMPLATES
    // ============================================

    private function getServiceDescriptionPrompt($currentDescription, $context = [])
    {
        $title = $context['title'] ?? '';
        $additionalContext = '';
        $locale = request('locale', app()->getLocale());
        
        if ($title) {
            $additionalContext = "\nService Title: \"{$title}\"";
        }

        return <<<PROMPT
Generate a professional service description for a service detail page based on this input:{$additionalContext}
"{$currentDescription}"

Rules:
- Write 3–6 short paragraphs using valid HTML <p>...</p> tags
- Do NOT use bullet points, numbered lists, markdown (**bold**), or headings
- Clearly explain what the service includes, key benefits, and how the process works
- Keep a professional, friendly tone suitable for a marketplace service page
- Do NOT repeat the title in every paragraph; mention it naturally only where needed
- No personal names, brand names, or locations unless already present in the input
- Write the description in the "{$locale}" language
- If the input is irrelevant to a service, respond exactly with: "INVALID_DESCRIPTION"

Output:
- Return ONLY the HTML content with multiple <p>...</p> blocks, nothing else.
PROMPT;
    }

    /**
     * Get service package description prompt (plain text, shorter)
     */
    private function getServicePackageDescriptionPrompt($currentDescription, $context = [])
    {
        $title = $context['title'] ?? '';
        $additionalContext = '';
        $locale = request('locale', app()->getLocale());
        
        if ($title) {
            $additionalContext = "\nPackage Title: \"{$title}\"";
        }

        return <<<PROMPT
Generate a professional service package description based on this input:{$additionalContext}
"{$currentDescription}"

Rules:
- Write 2-3 short paragraphs (80-120 words total)
- Plain text only - NO HTML tags, NO <p> tags, NO markdown
- Clearly explain what the package includes and key benefits
- Keep it concise and professional
- Do NOT repeat the title in every paragraph
- No personal names, brand names, or locations unless already present in the input
- Write the description in the "{$locale}" language
- If the input is irrelevant to a service package, respond exactly with: "INVALID_DESCRIPTION"

Output:
- Return ONLY plain text description, no HTML, no markdown, nothing else.
PROMPT;
    }

    private function getProductDescriptionPrompt($currentDescription, $context = [])
    {
        $title = $context['title'] ?? '';
        $features = $context['features'] ?? '';
        $additionalContext = '';
        
        if ($title) {
            $additionalContext .= "\nProduct Title: \"{$title}\"";
        }
        if ($features) {
            $additionalContext .= "\nKey Features: \"{$features}\"";
        }

        return <<<PROMPT
        Generate an e-commerce product description based on this input:{$additionalContext}
        "{$currentDescription}"

        Rules:
        - 150-200 words
        - Include specifications and benefits
        - SEO optimized
        - Use bullet points for features
        - Professional but persuasive
        - If irrelevant, respond with: "INVALID_DESCRIPTION"

        Output ONLY the description:
        PROMPT;
    }

//     private function getCategoryDescriptionPrompt($currentDescription)
//     {
//         return <<<PROMPT
// Generate a category description based on this input:
// "{$currentDescription}"

// Rules:
// - 80-120 words
// - Explain category scope
// - Mention typical items/services
// - SEO friendly
// - Professional tone
// - If irrelevant, respond with: "INVALID_DESCRIPTION"

// Output ONLY the description:
// PROMPT;
//     }

    private function getBlogDescriptionPrompt($currentDescription)
    {
        return <<<PROMPT
Generate a blog post description/abstract based on this input:
"{$currentDescription}"

Rules:
- 100-150 words
- Engaging introduction
- Summarize key points
- Encourage reading
- SEO optimized
- If irrelevant, respond with: "INVALID_DESCRIPTION"

Output ONLY the description:
PROMPT;
    }

    private function getPageDescriptionPrompt($currentDescription)
    {
        return <<<PROMPT
        Generate a web page description based on this input:
        "{$currentDescription}"

        Rules:
        - 100-150 words
        - Clear value proposition
        - Professional tone
        - Include important information
        - SEO friendly
        - If irrelevant, respond with: "INVALID_DESCRIPTION"

        Output ONLY the description:
        PROMPT;
    }

    private function getDefaultDescriptionPrompt($currentDescription, $contentType)
    {
        return <<<PROMPT
        Generate a professional description for a "{$contentType}" based on this input:
        "{$currentDescription}"

        Rules:
        - 100-150 words
        - Clear and informative
        - Professional tone
        - If irrelevant, respond with: "INVALID_DESCRIPTION"

        Output ONLY the description:
        PROMPT;
    }

    // ============================================
    // AI MODEL INTERACTION METHODS
    // ============================================

    /**
     * Test AI Model with custom prompt
     */
    private function testAIModel($model, $prompt)
    {
        try {
            if (empty($model->api_key)) {
                throw new Exception("API Key is required to test the model.");
            }
            
            // Prepare headers
            $headers = $model->headers ?? [];
            $headers = $this->replacePlaceholders($headers, $model);
            
            if (empty($headers)) {
                $headers = [
                    'Content-Type' => 'application/json',
                ];
                
                if ($model->provider === 'openai' || $model->provider === 'anthropic') {
                    $headers['Authorization'] = 'Bearer ' . $model->api_key;
                } elseif ($model->provider === 'google') {
                    $headers['x-goog-api-key'] = $model->api_key;
                } else {
                    $headers['Authorization'] = 'Bearer ' . $model->api_key;
                }
            }
            
            // Prepare params
            $params = $model->params ?? [];
            $params = $this->replacePlaceholders($params, $model);
            
            // Prepare payload
            $payload = $model->payload ?? [];
            
            if (empty($payload)) {
                $payload = $this->getDefaultAIPayload($model, $prompt);
            } else {
                $payload = $this->replacePlaceholders($payload, $model);
                $payload = $this->replacePromptInAIPayload($payload, $prompt);
                // Ensure prompt is injected in the correct location if it wasn't replaced
                $payload = $this->ensurePromptInPayload($payload, $model, $prompt);
            }
            
            $endpoint = $this->getAIEndpoint($model);
            
            if ($model->base_url) {
                $baseUrlClean = rtrim($model->base_url, '/');
                $hasV1 = (substr($baseUrlClean, -3) === '/v1');
                if ($hasV1 && strpos($endpoint, '/v1/') === 0) {
                    $endpoint = substr($endpoint, 3);
                }
                $url = $baseUrlClean . $endpoint;
            } else {
                $url = $this->getDefaultAIUrl($model) . $endpoint;
            }
            
            $client = new Client();
            $requestOptions = [
                'headers' => $headers,
                'timeout' => 30,
            ];
            
            if ($model->provider === 'google' && !isset($headers['x-goog-api-key']) && !empty($model->api_key)) {
                $url .= (strpos($url, '?') !== false ? '&' : '?') . 'key=' . urlencode($model->api_key);
            }
            
            if (strtoupper($this->getAIMethod($model)) === 'GET') {
                if (!empty($params)) {
                    $url .= '?' . http_build_query($params);
                }
            } else {
                if (!empty($payload)) {
                    $requestOptions['json'] = $payload;
                }
            }
            
            $response = $client->request($this->getAIMethod($model), $url, $requestOptions);
            
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

    /**
     * Get default AI payload
     */
    private function getDefaultAIPayload($model, $prompt)
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
                    'max_tokens' => $this->getMaxTokensForContent($prompt)
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
                        'maxOutputTokens' => $this->getMaxTokensForContent($prompt)
                    ]
                ];
            case 'anthropic':
                return [
                    'model' => $model->model_name ?? 'claude-3-haiku-20240307',
                    'max_tokens' => $this->getMaxTokensForContent($prompt),
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
                    'model' => $model->model_name ?? 'default',
                    'max_tokens' => $this->getMaxTokensForContent($prompt)
                ];
        }
    }

    /**
     * Determine max tokens based on prompt type
     */
    private function getMaxTokensForContent($prompt)
    {
        // Determine if this is for title or description or HTML content
        if (strpos($prompt, 'title') !== false || (strpos($prompt, 'Output ONLY the title') !== false) || strlen($prompt) < 100) {
            return 100; // Short for titles
        } elseif (strpos($prompt, 'meta') !== false || (strpos($prompt, 'meta description') !== false)) {
            return 200; // Medium for meta descriptions
        } elseif (strpos($prompt, 'HTML') !== false || strpos($prompt, '<p>') !== false || strpos($prompt, 'HTML content') !== false) {
            return 1500; // Longer for HTML-based content
        } else {
            return 500; // Default for descriptions
        }
    }

    /**
     * Replace placeholders in data (headers, params, payload)
     */
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

    /**
     * Replace prompt in AI payload
     */
    private function replacePromptInAIPayload($payload, $prompt)
    {
        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                if (is_array($value)) {
                    $payload[$key] = $this->replacePromptInAIPayload($value, $prompt);
                } else {
                    $payload[$key] = str_replace('{prompt}', $prompt, $value);
                }
            }
        }
        return $payload;
    }

    /**
     * Ensure prompt is in the correct location in payload based on provider
     */
    private function ensurePromptInPayload($payload, $model, $prompt)
    {
        if (!is_array($payload)) {
            return $payload;
        }

        // Based on provider, ensure prompt is in the correct location
        switch ($model->provider) {
            case 'openai':
                // For OpenAI, ensure prompt is in messages
                if (!isset($payload['messages']) || !is_array($payload['messages'])) {
                    $payload['messages'] = [];
                }
                
                // Find the last user message index
                $lastUserIndex = -1;
                foreach ($payload['messages'] as $index => $message) {
                    if (isset($message['role']) && $message['role'] === 'user') {
                        $lastUserIndex = $index;
                    }
                }
                
                // If no user message exists, append one
                if ($lastUserIndex < 0) {
                    $payload['messages'][] = [
                        'role' => 'user',
                        'content' => $prompt
                    ];
                } else {
                    // Update the last user message with the prompt
                    $payload['messages'][$lastUserIndex]['content'] = $prompt;
                    $payload['messages'][$lastUserIndex]['role'] = 'user';
                }
                break;

            case 'google':
                // For Google, prompt should be in contents[0].parts[0].text
                if (!isset($payload['contents']) || !is_array($payload['contents']) || empty($payload['contents'])) {
                    $payload['contents'] = [[
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]];
                } else {
                    // Ensure first content has the prompt
                    if (!isset($payload['contents'][0]['parts']) || !is_array($payload['contents'][0]['parts'])) {
                        $payload['contents'][0]['parts'] = [];
                    }
                    $payload['contents'][0]['parts'][0] = [
                        'text' => $prompt
                    ];
                }
                break;

            case 'anthropic':
                // For Anthropic, ensure prompt is in messages
                if (!isset($payload['messages']) || !is_array($payload['messages'])) {
                    $payload['messages'] = [];
                }
                
                // Find the last user message index
                $lastUserIndex = -1;
                foreach ($payload['messages'] as $index => $message) {
                    if (isset($message['role']) && $message['role'] === 'user') {
                        $lastUserIndex = $index;
                    }
                }
                
                // If no user message exists, append one
                if ($lastUserIndex < 0) {
                    $payload['messages'][] = [
                        'role' => 'user',
                        'content' => $prompt
                    ];
                } else {
                    // Update the last user message with the prompt
                    $payload['messages'][$lastUserIndex]['content'] = $prompt;
                    $payload['messages'][$lastUserIndex]['role'] = 'user';
                }
                break;

            default:
                // For other providers, try to set a generic prompt field
                if (!isset($payload['prompt']) || empty($payload['prompt'])) {
                    $payload['prompt'] = $prompt;
                }
                break;
        }

        return $payload;
    }

    /**
     * Get AI endpoint
     */
    private function getAIEndpoint($model)
    {
        switch ($model->provider) {
            case 'openai':
                return '/v1/chat/completions';
            case 'google':
                $modelName = $model->model_name ?? 'gemini-pro';
                return '/v1beta/models/' . $modelName . ':generateContent';
            case 'anthropic':
                return '/v1/messages';
            default:
                return '/v1/chat/completions';
        }
    }

    /**
     * Get default AI URL
     */
    private function getDefaultAIUrl($model)
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

    /**
     * Get AI method
     */
    private function getAIMethod($model)
    {
        return 'POST';
    }

    /**
     * Extract content from AI response
     */
    private function extractAIContent($result, $provider)
    {
        if (!isset($result['response'])) {
            throw new Exception("No response received from AI model");
        }

        $response = $result['response'];
        
        switch ($provider) {
            case 'openai':
                if (isset($response['choices'][0]['message']['content'])) {
                    return trim($response['choices'][0]['message']['content'], '\'"');
                }
                break;
                
            case 'google':
                if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($response['candidates'][0]['content']['parts'][0]['text'], '\'"');
                }
                break;
                
            case 'anthropic':
                if (isset($response['content'][0]['text'])) {
                    return trim($response['content'][0]['text'], '\'"');
                }
                break;
                
            default:
                if (is_string($response)) {
                    return trim($response, '\'"');
                } elseif (is_array($response)) {
                    foreach ($response as $value) {
                        if (is_string($value)) {
                            return trim($value, '\'"');
                        }
                    }
                }
        }
        
        if (isset($result['raw_response'])) {
            $raw = $result['raw_response'];
            if (is_string($raw)) {
                return trim($raw, '\'"');
            }
        }
        
        throw new Exception("Could not extract content from AI response");
    }

    /**
     * Parse keywords string into array
     */
    private function parseKeywordsString($keywords)
    {
        // Remove quotes and extra spaces
        $keywords = trim($keywords, '\'"');
        
        // Split by commas, semicolons, or new lines
        $keywordsArray = preg_split('/[,;|\n]+/', $keywords);
        
        // Trim each keyword and remove empty values
        $keywordsArray = array_map('trim', $keywordsArray);
        $keywordsArray = array_filter($keywordsArray);
        $keywordsArray = array_unique($keywordsArray);
        
        // Limit to first 5-10 keywords
        $keywordsArray = array_slice($keywordsArray, 0, 10);
        
        return array_values($keywordsArray);
    }

    /**
     * Generate multiple content types at once
     */
    public function generateAIContentBatch($content, $contentType = 'service', $generate = ['title', 'description', 'meta', 'keywords'])
    {
        $results = [];
        $context = ['content_type' => $contentType];
        
        if (in_array('title', $generate)) {
            $results['title'] = $this->generateAITitle($content, null, $context);
        }
        
        if (in_array('description', $generate)) {
            $descriptionContext = $context;
            if (isset($results['title']['data'])) {
                $descriptionContext['title'] = $results['title']['data'];
            }
            $results['description'] = $this->generateAIDescription($content, null, $descriptionContext);
        }
        
        if (in_array('meta', $generate)) {
            $results['meta'] = $this->generateAIMetaDescription($content, null, $contentType);
        }
        
        if (in_array('keywords', $generate)) {
            $results['keywords'] = $this->generateAIKeywords($content, null, $contentType);
        }
        
        return [
            'status' => true,
            'message' => 'Content generated successfully',
            'data' => $results
        ];
    }

    /**
     * Generate FAQs (questions and answers) using AI
     *
     * @param string $title Service title or main topic
     * @param string|null $promptTemplate
     * @param string $contentType
     * @param int $count Number of FAQs to generate
     * @return array
     */
    public function generateAIFAQ($title, $promptTemplate = null, $contentType = 'service', $count = 5)
    {
        try {
            $defaultModel = CustomAIModel::where('is_default', true)->first();

            if (!$defaultModel) {
                $defaultModel = CustomAIModel::first();

                if (!$defaultModel) {
                    throw new Exception("No AI model configured. Please configure an AI model first.");
                }
            }

            $prompt = $this->getFAQPrompt($title, $promptTemplate, $contentType, $count);
            $result = $this->testAIModel($defaultModel, $prompt);

            if (!$result['success']) {
                throw new Exception("AI Model Error: " . ($result['error'] ?? 'Unknown error'));
            }

            $faqRaw = $this->extractAIContent($result, $defaultModel->provider);

            // Try to decode as JSON first
            $decoded = json_decode($faqRaw, true);
            if (is_array($decoded)) {
                return [
                    'status' => true,
                    'message' => 'FAQs generated successfully',
                    'data' => $decoded
                ];
            }

            // Fallback: return raw string for frontend parsing
            return [
                'status' => true,
                'message' => 'FAQs generated successfully',
                'data' => $faqRaw
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Build FAQ prompt
     */
    private function getFAQPrompt($title, $promptTemplate = null, $contentType = 'service', $count = 5)
    {
        if ($promptTemplate) {
            return str_replace(['{{TITLE}}', '{{COUNT}}'], [$title, (string)$count], $promptTemplate);
        }

        $locale = request('locale', app()->getLocale());

        return <<<PROMPT
You are generating an FAQ section for a "{$contentType}" in a service marketplace.

Service Title:
"{$title}"

Rules:
- Generate exactly {$count} frequently asked questions and answers.
- Questions must be short, clear, and directly related to the service.
- Answers must be helpful, honest, and written in simple, user-friendly language.
- Do NOT mention personal names, brand names, or specific locations.
- Do NOT include pricing or discounts unless clearly implied by the title.
- Focus on things customers usually ask: scope of service, process, requirements, timing, and policies.
- Write all questions and answers in the "{$locale}" language.

Output format (strict JSON):

[
  {
    "question": "First question here?",
    "answer": "Clear and concise answer here."
  },
  {
    "question": "Second question here?",
    "answer": "Clear and concise answer here."
  }
  // ... more items until you have {$count} items total
]

Respond with ONLY the JSON array, nothing else.
PROMPT;
    }
}