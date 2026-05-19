<?php

namespace App\Repositories\API;

use Exception;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;
use App\Exceptions\ExceptionHandler;
use App\Models\User;
use Prettus\Repository\Eloquent\BaseRepository;

class OpenAIRepository extends BaseRepository
{
    protected $notification;

    public function model()
    {
        return User::class;
    }

    public function generateText($request)
    {
        $inputText = $request['input_text'];
        $locale    = $request['locale'];
        $type      = $request['type'];

        // Determine which prompt template to use
        switch ($type) {
            case 'service_title':
                $prompt = $this->serviceTitlePrompt($inputText, $locale);
                break;

            default:
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid prompt type.'
                ], 400);
        }

        try {
            // OpenAI API Call
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.4,
                'max_tokens'  => 120
            ]);
            $resultText = trim($response->choices[0]->message->content ?? '');

            return response()->json([
                'status' => true,
                'type'   => $type,
                'data'   => $resultText
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'OpenAI request failed.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

     /**
     * Prompt Template: Service Title
     */
    private function serviceTitlePrompt($context, $langCode)
    {
        return <<<PROMPT
            You are an expert content generator for an online service-booking platform.

            Your task is to produce a clean and professional service title based on the input: "{$context}".
            The output must follow STRICT rules.

            LANGUAGE RULES:
            - Generate the result entirely in {$langCode}. This is mandatory.
            - If the input is in another language, translate it into {$langCode} while keeping the intended meaning.
            - Do NOT mix languages. Use only words valid for {$langCode}.
            - Output must be plain text only, with no punctuation unless required by grammar.

            TITLE RULES:
            - Keep the title clear, short, and suitable for a service marketplace.
            - Length should be roughly 35–70 characters.
            - Do not add marketing phrases, emojis, or filler words.
            - Only generate titles for services that people can book (repairs, cleaning, installation, beauty services, shifting, plumbing, maintenance, technical support, etc.).

            VALIDATION RULES:
            Return only "INVALID_INPUT" if ANY of the following are true:
            - The text refers to food, fruits, vegetables, groceries, clothing, animals, places, electronics, vehicles, gadgets, or general products.
            - The text represents a person’s name, celebrity, or individual identity.
            - The text includes brand names, app names, company names, or copyrighted characters.
            - The input cannot reasonably be interpreted as a real-world service that can be booked.
            - The wording is unclear, random, or cannot be rewritten as a professional service title.

            OUTPUT:
            - Return ONLY the final title in {$langCode}, or "INVALID_INPUT".
            - No explanations, no notes, no additional text.
            PROMPT;
    }
}
