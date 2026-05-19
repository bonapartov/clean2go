<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\OpenAIGenerateRequest;
use App\Repositories\API\OpenAIRepository;

class OpenAIController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $repository;

    public function __construct(OpenAIRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generateText(OpenAIGenerateRequest $request)
    {
        return $this->repository->generateText($request);
    }
}
