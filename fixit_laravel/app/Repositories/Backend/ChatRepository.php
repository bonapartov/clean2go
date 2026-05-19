<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use Exception;
use App\Models\User;
use App\Http\Traits\FireStoreTrait;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use Prettus\Repository\Eloquent\BaseRepository;

class ChatRepository extends BaseRepository
{
    use FireStoreTrait;

    private $database;

    public function model()
    {
        return User::class;
    }

    public function index()
    {
        try {

            if(Helpers::getCurrentRoleName() === RoleEnum::PROVIDER || Helpers::getCurrentRoleName() === RoleEnum::SERVICEMAN) {
                
                $user = auth()->user();
                $admin = User::role(RoleEnum::ADMIN)->with('media')->first();

                return view('backend.chat.support-chat', [
                    'user' => $user,
                    'admin' => $admin
                ]);

            } else {

                $currentUserId = Helpers::getAdminId();
    
                $users      = User::whereNull('deleted_at')->role(RoleEnum::CONSUMER)->where('id', '!=', $currentUserId)->with('media')->get();
                $providers     = User::whereNull('deleted_at')->role(RoleEnum::PROVIDER)->where('id', '!=', $currentUserId)->with('media')->get();
                $servicemen     = User::whereNull('deleted_at')->role(RoleEnum::SERVICEMAN)->where('id', '!=', $currentUserId)->with('media')->get();
                $recentChats = [];
                
                $chatDocs = $this->fireStoreQueryCollection('support_chats', [
                    ['participants', 'array-contains', (string) $currentUserId],
                ], [
                    'orderBy' => ['created_at', 'desc'],
                    'limit'   => 50,
                ]);
                
                foreach ($chatDocs as $doc) {
                    $chatId   = $doc['id'];
                    $chatData = $doc;
                    if (!isset($chatData['participants']) || ! is_array($chatData['participants'])) {
                        throw new Exception('Invalid participants field in document ' . $chatId, 500);
                    }
    
                    $otherUserIds = array_diff($chatData['participants'], [$currentUserId]);
                    $otherUserId  = reset($otherUserIds);
                    if (!$otherUserId) {
                        throw new Exception('No other user found in participants for document ' . $chatId, 500);
                    }
    
                    $user = User::find($otherUserId);
                    if ($user) {
                        $recentChats[$otherUserId] = [
                            'user_id' => $otherUserId,
                            'name'    => $user->name,
                            'image'   => $user->media?->first()?->original_url ?? null,
                            'role'    => $user->role?->name ?? 'user',
                            'chat_id' => $chatId,
                        ];
                    }
                }
    
                return view('backend.chat.index', ['users' => $users, 'providers' => $providers, 'servicemen' => $servicemen, 'recentChats' => $recentChats]);
            }

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
