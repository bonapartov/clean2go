<?php

namespace App\Repositories\Frontend;

use Exception;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Http\Traits\FireStoreTrait;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class ChatRepository extends BaseRepository
{
    use FireStoreTrait;

    public function model()
    {
        return User::class;
    }

    public function index()
    {
        try {
            $user = auth()->user();

            $admin = User::role(RoleEnum::ADMIN)->with('media')->first();

            return view('frontend.account.chat', [
                'user' => $user,
                'admin' => $admin
            ]);

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}