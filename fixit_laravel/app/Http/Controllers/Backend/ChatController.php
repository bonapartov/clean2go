<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\ChatRepository;

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
