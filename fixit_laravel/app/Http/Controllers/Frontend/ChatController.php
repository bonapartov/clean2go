<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Frontend\ChatRepository;

class ChatController extends Controller
{
  public $repository;
    /**
     * Display a listing of the resource.
     */
    public function __construct(ChatRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
      return $this->repository->index();
    }
    
}
